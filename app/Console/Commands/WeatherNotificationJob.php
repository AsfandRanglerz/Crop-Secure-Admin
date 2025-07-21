<?php

namespace App\Console\Commands;

use App\Helpers\WeatherNotificationHelper;
use App\Models\CropInsurance;
use App\Models\EnsuredCropName;
use App\Models\InsuranceHistory;
use App\Models\VillageCrop;
use App\Models\VillageWeatherDailySummary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WeatherNotificationJob extends Command
{
    protected $signature = 'notify:weather-alerts';
    protected $description = 'Send weather alerts to farmers based on last 14 days of village weather data';

    public function handle()
    {
        $today = now()->toDateTimeString();
        Log::info("🗖️ Weather Notification Job started at: $today");

        // Step 1: Fetch weather for each village
        $weatherController = new \App\Http\Controllers\Admin\WeatherController();
        $villages = \App\Models\Village::all();

        foreach ($villages as $village) {
            $weatherController->fetchTodayWeather($village->id);
            Log::info("🌦️ Weather fetched for village ID: {$village->id}");
        }

        // Step 2: Alert farmers
        $insurances = InsuranceHistory::with('user')
            ->where('insurance_type', 'Weather Index')
            ->get();

        foreach ($insurances as $insurance) {
            Log::info("🔍 Processing insurance ID: {$insurance->id}, User ID: {$insurance->user_id}");

            if ($insurance->last_temperature_alert_sent_at || $insurance->last_rainfall_alert_sent_at) {
                Log::info("⏩ Alert already sent before. Skipping user_id: {$insurance->user_id}");
                continue;
            }

            $userId = $insurance->user_id;
            $cropInsurance = CropInsurance::where('user_id', $userId)->first();
            if (!$cropInsurance || !$cropInsurance->village_id) {
                Log::warning("🚫 No CropInsurance or village_id for user_id: $userId");
                continue;
            }

            $villageId = $cropInsurance->village_id;
            $crop = EnsuredCropName::find($insurance->crop_id);
            if (!$crop || !$crop->harvest_start_time || !$crop->harvest_end_time) {
                Log::warning("🚫 Invalid crop or missing harvest dates for crop_id: {$insurance->crop_id}");
                continue;
            }

            $start = Carbon::createFromFormat('F', $crop->harvest_start_time)->startOfMonth();
            $end = Carbon::createFromFormat('F', $crop->harvest_end_time)->endOfMonth();

            if (!now()->between($start, $end)) {
                Log::info("⏩ Skipping user_id $userId: Not in harvest period.");
                continue;
            }

            $villageCrop = VillageCrop::where('village_id', $villageId)->first();
            $expectedTemp = $villageCrop?->avg_temp ?? 30;
            $expectedRain = $villageCrop?->avg_rainfall ?? 20;

            Log::info("📊 Admin Avg Temp: {$expectedTemp}°C, Rainfall: {$expectedRain}mm");

            $dailyData = VillageWeatherDailySummary::where('village_id', $villageId)
                ->whereBetween('date', [now()->subDays(14)->toDateString(), now()->toDateString()])
                ->get();

            if ($dailyData->isEmpty()) {
                Log::warning("🌤️ No daily summary found for village_id: $villageId");
                continue;
            }

            $abnormalTemp = false;
            $abnormalRain = false;

            // Check if any temp is 20% above average
            foreach ($dailyData as $day) {
                if ($day->temperature > $expectedTemp * 1.2) {
                    $abnormalTemp = true;
                    break;
                }
            }

            // Check if 14-day rainfall is >150% or <50% of expected
            $totalRainfall = $dailyData->sum('avg_rainfall');
            $expectedTotalRainfall = $expectedRain;
            if (
                $totalRainfall < $expectedTotalRainfall * 0.5 ||
                $totalRainfall > $expectedTotalRainfall * 1.5
            ) {
                $abnormalRain = true;
            }

            $farmer = $insurance->user;
            if (!$farmer || !$farmer->fcm_token) {
                Log::warning("🚫 Missing user or FCM token for user_id: $userId");
                continue;
            }

            $todayDate = now()->toDateString();
            $lossFlag = false;
            $comp = 0;

            if ($abnormalTemp) {
                WeatherNotificationHelper::notifyFarmer(
                    $farmer,
                    "🌡️ Temperature Alert\nAdmin Avg: {$expectedTemp}°C\nYour Village exceeded 20% threshold.\nLoss may occur."
                );
                Log::info("📨 Temp alert sent to user_id: $userId");
                $insurance->last_temperature_alert_sent_at = $todayDate;
                $lossFlag = true;
                $comp += 0.25 * $insurance->sum_insured;
            }

            if ($abnormalRain) {
                WeatherNotificationHelper::notifyFarmer(
                    $farmer,
                    "🌧️ Rainfall Alert\nAdmin Avg: {$expectedRain}mm/day\nYour Village's 14-day total: {$totalRainfall}mm\nLoss may occur."
                );
                Log::info("📨 Rainfall alert sent to user_id: $userId");
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

            // No-loss notification at end of season
            if (
                !$abnormalTemp && !$abnormalRain &&
                now()->toDateString() === $end->toDateString() &&
                !$insurance->last_temperature_alert_sent_at &&
                !$insurance->last_rainfall_alert_sent_at
            ) {
                WeatherNotificationHelper::notifyFarmer(
                    $farmer,
                    "✅ No loss detected during your crop season.\nAdmin Avg: Temp {$expectedTemp}°C, Rainfall {$expectedRain}mm/day\nWeather was stable."
                );
                Log::info("📨 No-loss alert sent to user_id: $userId");
            }

            $insurance->save();
        }

        Log::info("✅ Weather Notification Job completed for date: $today");
        $this->info('✅ 14-day weather notifications processed.');
    }
}
