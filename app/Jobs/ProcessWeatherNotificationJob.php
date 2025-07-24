<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Helpers\WeatherNotificationHelper;
use App\Models\CropInsurance;
use App\Models\EnsuredCropName;
use App\Models\InsuranceHistory;
use App\Models\VillageCrop;
use App\Models\VillageWeatherDailySummary;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessWeatherNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $today = now()->toDateTimeString();
        Log::info("🗖️ [Queued] Weather Notification Job started at: $today");

        $weatherController = new \App\Http\Controllers\Admin\WeatherController();
        $villages = \App\Models\Village::all();

        foreach ($villages as $village) {
            $weatherController->fetchTodayWeather($village->id);
            Log::info("🌦️ Weather fetched for village ID: {$village->id}");
        }

        $insurances = InsuranceHistory::with('user')
            ->where('insurance_type', 'Weather Index')
            ->get();

        foreach ($insurances as $insurance) {
            Log::info("🔍 Processing insurance ID: {$insurance->id}, User ID: {$insurance->user_id}");

            if ($insurance->last_temperature_alert_sent_at || $insurance->last_rainfall_alert_sent_at) {
                continue;
            }

            $userId = $insurance->user_id;
            $cropInsurance = CropInsurance::where('user_id', $userId)->first();
            if (!$cropInsurance || !$cropInsurance->village_id) continue;

            $villageId = $cropInsurance->village_id;
            $crop = EnsuredCropName::find($insurance->crop_id);
            if (!$crop || !$crop->harvest_start_time || !$crop->harvest_end_time) continue;

            $start = Carbon::createFromFormat('F', $crop->harvest_start_time)->startOfMonth();
            $end = Carbon::createFromFormat('F', $crop->harvest_end_time)->endOfMonth();

            if (!now()->between($start, $end)) continue;

            $villageCrop = VillageCrop::where('village_id', $villageId)->first();
            $expectedTemp = $villageCrop?->avg_temp ?? 30;
            $expectedRain = $villageCrop?->avg_rainfall ?? 20;

            $dailyData = VillageWeatherDailySummary::where('village_id', $villageId)
                ->whereBetween('date', [now()->subDays(14)->toDateString(), now()->toDateString()])
                ->get();

            if ($dailyData->isEmpty()) continue;

            $abnormalTemp = false;
            $abnormalRain = false;

            foreach ($dailyData as $day) {
                if ($day->temperature > $expectedTemp * 1.2) {
                    $abnormalTemp = true;
                    break;
                }
            }

            $totalRainfall = $dailyData->sum('avg_rainfall');
            if (
                $totalRainfall < $expectedRain * 0.5 ||
                $totalRainfall > $expectedRain * 1.5
            ) {
                $abnormalRain = true;
            }

            $farmer = $insurance->user;
            if (!$farmer || !$farmer->fcm_token) continue;

            $lossFlag = false;
            $comp = 0;

            if ($abnormalTemp) {
                WeatherNotificationHelper::notifyFarmer(
                    $farmer,
                    "🌡️ Temperature Alert\nAdmin Avg: {$expectedTemp}°C\nVillage exceeded 20%.\nLoss may occur."
                );
                $insurance->last_temperature_alert_sent_at = now()->toDateString();
                $lossFlag = true;
                $comp += 0.25 * $insurance->sum_insured;
            }

            if ($abnormalRain) {
                WeatherNotificationHelper::notifyFarmer(
                    $farmer,
                    "🌧️ Rainfall Alert\nAdmin Avg: {$expectedRain}mm/day\nVillage's 14-day total: {$totalRainfall}mm\nLoss may occur."
                );
                $insurance->last_rainfall_alert_sent_at = now()->toDateString();
                $lossFlag = true;
                $comp += 0.25 * $insurance->sum_insured;
            }

            $comp = min($comp, $insurance->sum_insured);

            if ($insurance->claimed_at === null && $lossFlag && $comp > 0) {
                $insurance->compensation_amount = round($comp, 2);
                $insurance->remaining_amount = round($comp, 2);
            }

            if (
                !$abnormalTemp && !$abnormalRain &&
                now()->toDateString() === $end->toDateString() &&
                !$insurance->last_temperature_alert_sent_at &&
                !$insurance->last_rainfall_alert_sent_at
            ) {
                WeatherNotificationHelper::notifyFarmer(
                    $farmer,
                    "✅ No loss during season. Avg: Temp {$expectedTemp}°C, Rainfall {$expectedRain}mm/day"
                );
            }

            $insurance->save();
        }

        Log::info("✅ Weather Notification Job completed (queued)");
    }
}
