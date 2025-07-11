<?php

namespace App\Console\Commands;

use App\Helpers\WeatherNotificationHelper;
use App\Models\CropInsurance;
use App\Models\EnsuredCropName;
use App\Models\InsuranceHistory;
use App\Models\VillageWeatherHistory;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WeatherNotificationJob extends Command
{
    protected $signature = 'notify:weather-alerts';
    protected $description = 'Send weather alerts to farmers based on last 14 days of village weather data';

    public function handle()
    {
        $today = now()->toDateString();

        Log::info("📆 Weather Notification Job started for date: $today");

        // Step 1: Get all Weather Index insurance buyers
        $insurances = InsuranceHistory::with('user')
            ->where('insurance_type', 'Weather Index')
            ->get();

        Log::info("📦 Total Weather Index insurances found: " . $insurances->count());

        foreach ($insurances as $insurance) {
            Log::info("🔍 Processing insurance ID: {$insurance->id}, User ID: {$insurance->user_id}");

            $userId = $insurance->user_id;

            // Step 2: Get village_id from CropInsurance
            $cropInsurance = CropInsurance::where('user_id', $userId)->first();
            if (!$cropInsurance || !$cropInsurance->village_id) {
                Log::warning("🚫 No CropInsurance or missing village_id for user_id: $userId");
                continue;
            }

            $villageId = $cropInsurance->village_id;
            Log::info("📍 Village ID for user_id $userId: $villageId");

            // Step 3: Get crop info
            $crop = EnsuredCropName::find($insurance->crop_id);
            if (!$crop || !$crop->harvest_start_time || !$crop->harvest_end_time) {
                Log::warning("🚫 Invalid crop or missing harvest dates for crop_id: {$insurance->crop_id}");
                continue;
            }

            $start = Carbon::createFromFormat('F', $crop->harvest_start_time)->startOfMonth();
            $end = Carbon::createFromFormat('F', $crop->harvest_end_time)->endOfMonth();

            Log::info("🌾 Crop harvest period for crop_id {$crop->id}: $start to $end");

            // Step 4: Check if within crop period
            if (!now()->between($start, $end)) {
                Log::info("⏩ Skipping user_id $userId: Current date not in harvest period.");
                continue;
            }

            // Step 5: Get last 14 days of weather data
            $weatherLast14Days = VillageWeatherHistory::where('village_id', $villageId)
                ->whereBetween('date', [now()->subDays(14)->toDateString(), now()->toDateString()])
                ->get();

            if ($weatherLast14Days->isEmpty()) {
                Log::warning("🌤️ No weather data for last 14 days for village_id $villageId");
                continue;
            }

            $expectedTemp = $crop->avg_temp ?? 30;
            $expectedRain = $crop->avg_rainfall ?? 20;
            Log::info("📊 Expected - Temp: {$expectedTemp}°C, Rainfall: {$expectedRain}mm");

            $abnormalTemp = false;
            $abnormalRain = false;

            foreach ($weatherLast14Days as $dayWeather) {
                if ($dayWeather->temperature > $expectedTemp + 5) {
                    $abnormalTemp = true;
                }

                if ($dayWeather->rainfall < $expectedRain * 0.5 || $dayWeather->rainfall > $expectedRain * 1.5) {
                    $abnormalRain = true;
                }

                if ($abnormalTemp && $abnormalRain) {
                    break;
                }
            }

            // Step 6: Get farmer
            $farmer = $insurance->user;
            if (!$farmer || !$farmer->fcm_token) {
                Log::warning("🚫 Missing user or FCM token for user_id: $userId");
                continue;
            }

            // Step 7: Notification logic with 14-day abnormal checks
            $todayDate = now()->toDateString();
            $lossFlag = false;
            $comp = 0;

            // 🔥 Temperature Alert
            if ($abnormalTemp) {
                if ($insurance->last_temperature_alert_sent_at !== $todayDate) {
                    WeatherNotificationHelper::notifyFarmer(
                        $farmer,
                        "Temperature Alert: Abnormal temperature detected in your area during the last 14 days."
                    );
                    Log::info("📨 Temperature alert sent to user_id: $userId");

                    $insurance->last_temperature_alert_sent_at = $todayDate;
                    $lossFlag = true;
                    $comp += 0.25 * $insurance->sum_insured;
                } else {
                    Log::info("⏳ Temperature alert already sent today for user_id: $userId");
                }
            }

            // 🌧️ Rainfall Alert
            if ($abnormalRain) {
                if ($insurance->last_rainfall_alert_sent_at !== $todayDate) {
                    WeatherNotificationHelper::notifyFarmer(
                        $farmer,
                        "Rainfall Alert: Abnormal rainfall observed in your village over the last 14 days."
                    );
                    Log::info("📨 Rainfall alert sent to user_id: $userId");

                    $insurance->last_rainfall_alert_sent_at = $todayDate;
                    $lossFlag = true;
                    $comp += 0.25 * $insurance->sum_insured;
                } else {
                    Log::info("⏳ Rainfall alert already sent today for user_id: $userId");
                }
            }

            // Cap compensation
            $comp = min($comp, $insurance->sum_insured);

            // Save compensation and alert flags only if not claimed
            if ($insurance->claimed_at === null && $lossFlag && $comp > 0) {
                $insurance->compensation_amount = round($comp, 2);
                $insurance->remaining_amount = round($comp, 2);
                Log::info("💰 Compensation updated for insurance ID {$insurance->id}");
            } else {
                Log::info("🚫 Skipping compensation update for insurance ID {$insurance->id} because already claimed.");
            }

            // Save any changes (including alert timestamps)
            $insurance->save();
        }

        Log::info("✅ Weather Notification Job completed for date: $today");
        $this->info('✅ 14-day weather notifications sent successfully.');
    }
}
