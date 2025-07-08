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

            $highTempDays = VillageWeatherHistory::where('village_id', $village->id)
                ->where('date', '>=', now()->subDays(13)->toDateString())
                ->where('temperature', '>=', $avgTemp * 1.2)
                ->count();

            $rainfall = VillageWeatherHistory::where('village_id', $village->id)
                ->where('date', '>=', now()->subDays(13)->toDateString())
                ->sum('rainfall');

            $farmerIds = CropInsurance::where('village_id', $village->id)
                ->pluck('user_id')
                ->unique();

            $farmers = Farmer::whereIn('id', $farmerIds)
                ->whereNotNull('fcm_token')
                ->get();

            if ($highTempDays === 14) {
                foreach ($farmers as $farmer) {
                    WeatherNotificationHelper::notifyFarmer(
                        $farmer,
                        'Recent weather analysis confirms that unusual temperature conditions have affected crops in your village. Since you are insured, you are eligible to submit a claim under your crop insurance coverage'
                    );
                }
            }

            if ($rainfall >= $avgRain * 1.5 || $rainfall <= $avgRain * 0.5) {
                foreach ($farmers as $farmer) {
                    WeatherNotificationHelper::notifyFarmer(
                        $farmer,
                        'Recent weather analysis confirms that your village has experienced abnormal rainfall levels, which may have caused damage to your crops. Since you are insured, you are eligible to submit a claim under your crop insurance coverage.'
                    );
                }
            }
        }

        $this->info('Weather data stored & notifications (if any) sent.');
    }
}
