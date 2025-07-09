<?php

namespace App\Console\Commands;

use App\Helpers\WeatherNotificationHelper;
use App\Models\CropInsurance;
use App\Models\Farmer;
use App\Models\Village;
use App\Models\VillageWeatherHistory;
use Illuminate\Console\Command;

class WeatherNotificationJob extends Command
{
    protected $signature = 'notify:weather-alerts';
    protected $description = 'Send weather alerts to farmers based on last 14 days data';

    public function handle()
    {
        $villages = Village::with('villageCrops')->get();
        $weatherController = new \App\Http\Controllers\Admin\WeatherController();

        foreach ($villages as $village) {
            $weatherController->fetchTodayWeather($village->id);

            $cropData = $village->villageCrops()->first();
            if (!$cropData) continue;

            $avgTemp = $cropData->avg_temp;
            $avgRain = $cropData->avg_rainfall;

            $recentWeather = VillageWeatherHistory::where('village_id', $village->id)
                ->where('date', '>=', now()->subDays(13)->toDateString())
                ->get();

            $avgRecordedTemp = $recentWeather->avg('temperature');
            $avgRecordedRainfall = $recentWeather->avg('rainfall');

            $expectedTemp = $cropData->avg_temp;
            $expectedRain = $cropData->avg_rainfall;

            $farmers = Farmer::whereIn('id', CropInsurance::where('village_id', $village->id)
                ->pluck('user_id')
                ->unique())
                ->whereNotNull('fcm_token')
                ->get();

            if ($avgRecordedTemp > $expectedTemp) {
                foreach ($farmers as $farmer) {
                    WeatherNotificationHelper::notifyFarmer(
                        $farmer,
                        "⚠️ Temperature Alert: The average temperature over the past 14 days is {$avgRecordedTemp}°C, which is higher than the expected {$expectedTemp}°C. You may be eligible to submit a crop insurance claim."
                    );
                }
            }

            if ($avgRecordedRainfall >= $expectedRain * 1.5 || $avgRecordedRainfall <= $expectedRain * 0.5) {
                foreach ($farmers as $farmer) {
                    WeatherNotificationHelper::notifyFarmer(
                        $farmer,
                        "🌧️ Rainfall Alert: The average rainfall over the past 14 days is {$avgRecordedRainfall}mm, which is abnormal compared to the expected {$expectedRain}mm. You may be eligible to submit a crop insurance claim."
                    );
                }
            }
        }

        $this->info('Weather data stored & notifications (if any) sent.');
    }
}
