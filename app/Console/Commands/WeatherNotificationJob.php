<?php

namespace App\Console\Commands;

use App\Helpers\WeatherNotificationHelper;
use App\Models\CropInsurance;
use App\Models\EnsuredCropName;
use App\Models\Farmer;
use App\Models\InsuranceHistory;
use App\Models\VillageWeatherHistory;
use Illuminate\Console\Command;
use Carbon\Carbon;

class WeatherNotificationJob extends Command
{
    protected $signature = 'notify:weather-alerts';
    protected $description = 'Send weather alerts to farmers based on today\'s village weather data';

    public function handle()
    {
        $today = now()->toDateString();

        // Step 1: Get all Weather Index insurance buyers
        $insurances = InsuranceHistory::with('user')->where('insurance_type', 'Weather Index')->get();

        foreach ($insurances as $insurance) {
            $userId = $insurance->user_id;

            // Step 2: Get village_id from CropInsurance
            $cropInsurance = CropInsurance::where('user_id', $userId)->first();
            if (!$cropInsurance || !$cropInsurance->village_id) continue;

            $villageId = $cropInsurance->village_id;

            // Step 3: Get crop info
            $crop = EnsuredCropName::find($insurance->crop_id);
            if (!$crop || !$crop->harvest_start_date || !$crop->harvest_end_date) continue;

            $start = Carbon::parse($crop->harvest_start_date);
            $end = Carbon::parse($crop->harvest_end_date);

            // Step 4: Check if within crop period
            if (!now()->between($start, $end)) continue;

            // Step 5: Get today's weather data
            $todayWeather = VillageWeatherHistory::where('village_id', $villageId)
                ->whereDate('date', $today)
                ->first();

            if (!$todayWeather) continue;

            $temperature = $todayWeather->temperature;
            $rainfall = $todayWeather->rainfall;

            $expectedTemp = $crop->avg_temp ?? 30;
            $expectedRain = $crop->avg_rainfall ?? 20;

            // Step 6: Get farmer
            $farmer = $insurance->user;
            if (!$farmer || !$farmer->fcm_token) continue;

            // Step 7: Notify based on today's weather
            if ($temperature > $expectedTemp + 5) {
                WeatherNotificationHelper::notifyFarmer(
                    $farmer,
                    "⚠️ Temperature Alert: Today's temperature is {$temperature}°C, higher than expected ({$expectedTemp}°C)."
                );
            }

            if ($rainfall < $expectedRain * 0.5 || $rainfall > $expectedRain * 1.5) {
                WeatherNotificationHelper::notifyFarmer(
                    $farmer,
                    "🌧️ Rainfall Alert: Today's rainfall is {$rainfall}mm, which is abnormal compared to expected ({$expectedRain}mm)."
                );
            }
        }

        $this->info('✅ 1-day weather notifications sent successfully.');
    }
}
