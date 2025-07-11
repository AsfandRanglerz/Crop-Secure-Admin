<?php

namespace App\Console\Commands;

use App\Helpers\WeatherNotificationHelper;
use App\Models\CropInsurance;
use App\Models\EnsuredCropName;
use App\Models\InsuranceHistory;
use App\Models\VillageCrop;
use App\Models\VillageWeatherHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WeatherNotificationJob extends Command
{
    protected $signature = 'notify:weather-alerts';
    protected $description = 'Send weather alerts to farmers based on last 14 days of village weather data';

    public function handle()
    {
        $today = now()->toDateString();
        Log::info("📆 Weather Notification Job started for date: $today");

        $insurances = InsuranceHistory::with('user')->where('insurance_type', 'Weather Index')->get();
        Log::info("📦 Total Weather Index insurances found: " . $insurances->count());

        foreach ($insurances as $insurance) {
            Log::info("🔍 Processing insurance ID: {$insurance->id}, User ID: {$insurance->user_id}");

            $userId = $insurance->user_id;
            $cropInsurance = CropInsurance::where('user_id', $userId)->first();
            if (!$cropInsurance || !$cropInsurance->village_id) {
                Log::warning("🚫 No CropInsurance or village_id for user_id: $userId");
                continue;
            }

            $villageId = $cropInsurance->village_id;

            // Get crop harvest time period
            $crop = EnsuredCropName::find($insurance->crop_id);
            if (!$crop || !$crop->harvest_start_time || !$crop->harvest_end_time) {
                Log::warning("🚫 Invalid crop or missing harvest dates for crop_id: {$insurance->crop_id}");
                continue;
            }

            $start = Carbon::createFromFormat('F', $crop->harvest_start_time)->startOfMonth();
            $end = Carbon::createFromFormat('F', $crop->harvest_end_time)->endOfMonth();

            if (!now()->between($start, $end)) {
                Log::info("⏩ Skipping user_id $userId: Current date not in harvest period.");
                continue;
            }

            $villageCrop = VillageCrop::where('village_id', $villageId)->first();
            $expectedTemp = $villageCrop?->avg_temp ?? 30;
            $expectedRain = $villageCrop?->avg_rainfall ?? 20;
            Log::info("📊 Avg (Admin Set) Temp: {$expectedTemp}°C, Rain: {$expectedRain}mm");

            $weatherLast14Days = VillageWeatherHistory::where('village_id', $villageId)
                ->whereBetween('date', [now()->subDays(14)->toDateString(), now()->toDateString()])
                ->get();

            if ($weatherLast14Days->isEmpty()) {
                Log::warning("🌤️ No weather data for last 14 days for village_id $villageId");
                continue;
            }

            $abnormalTemp = false;
            $abnormalRain = false;

            foreach ($weatherLast14Days as $dayWeather) {
                if ($dayWeather->temperature > $expectedTemp + 5) {
                    $abnormalTemp = true;
                }
                if ($dayWeather->rainfall < $expectedRain * 0.5 || $dayWeather->rainfall > $expectedRain * 1.5) {
                    $abnormalRain = true;
                }

                if ($abnormalTemp && $abnormalRain) break;
            }

            $farmer = $insurance->user;
            if (!$farmer || !$farmer->fcm_token) {
                Log::warning("🚫 Missing user or FCM token for user_id: $userId");
                continue;
            }

            $todayDate = now()->toDateString();
            $lossFlag = false;
            $comp = 0;

            if ($abnormalTemp && $insurance->last_temperature_alert_sent_at !== $todayDate) {
                WeatherNotificationHelper::notifyFarmer(
                    $farmer,
                    "🌡️ Temperature Alert\nAdmin Avg: {$expectedTemp}°C\nYour Village Avg (14 days): High\nLoss may occur."
                );
                Log::info("📨 Temp alert sent to user_id: $userId");
                $insurance->last_temperature_alert_sent_at = $todayDate;
                $lossFlag = true;
                $comp += 0.25 * $insurance->sum_insured;
            }

            if ($abnormalRain && $insurance->last_rainfall_alert_sent_at !== $todayDate) {
                WeatherNotificationHelper::notifyFarmer(
                    $farmer,
                    "🌧️ Rainfall Alert\nAdmin Avg: {$expectedRain}mm\nYour Village Avg (14 days): Abnormal\nLoss may occur."
                );
                Log::info("📨 Rain alert sent to user_id: $userId");
                $insurance->last_rainfall_alert_sent_at = $todayDate;
                $lossFlag = true;
                $comp += 0.25 * $insurance->sum_insured;
            }

            $comp = min($comp, $insurance->sum_insured);

            if ($insurance->claimed_at === null && $lossFlag && $comp > 0) {
                $insurance->compensation_amount = round($comp, 2);
                $insurance->remaining_amount = round($comp, 2);
                Log::info("💰 Compensation updated for insurance ID {$insurance->id}");
            }

            // 🔔 No loss notification at end of period
            if (
                !$abnormalTemp && !$abnormalRain &&
                now()->toDateString() === $end->toDateString() &&
                !$insurance->last_temperature_alert_sent_at &&
                !$insurance->last_rainfall_alert_sent_at
            ) {
                WeatherNotificationHelper::notifyFarmer(
                    $farmer,
                    "✅ No loss detected during your crop's period.\nAdmin Avg: Temp {$expectedTemp}°C, Rain {$expectedRain}mm\nWeather was stable in your village."
                );
                Log::info("📨 No loss alert sent to user_id: $userId");
            }

            $insurance->save();
        }

        Log::info("✅ Weather Notification Job completed for date: $today");
        $this->info('✅ 14-day weather notifications processed.');
    }
}
