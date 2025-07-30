<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Village;
use App\Models\VillageWeatherHistory;
use App\Models\VillageWeatherDailySummary;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherController extends Controller
{
    public function fetchTodayWeather($villageId)
    {
        $village = Village::find($villageId);
        if (!$village || !$village->latitude || !$village->longitude) return;

        $lat = $village->latitude;
        $lon = $village->longitude;
        $apiKey = '1e6e6235ad62763cd112c7a3adda011e';

        $response = Http::get("https://api.openweathermap.org/data/2.5/weather", [
            'lat' => $lat,
            'lon' => $lon,
            'units' => 'metric',
            'appid' => $apiKey,
        ]);

        if ($response->failed()) return;

        $data = $response->json();
        $temperature = $data['main']['temp'] ?? null;
        $weatherMain = $data['weather'][0]['main'] ?? '';

        $rainfall = 0;
        if (!empty($data['rain'])) {
            if (isset($data['rain']['1h'])) {
                $rainfall = (float) $data['rain']['1h'];
            } elseif (isset($data['rain']['3h'])) {
                $rainfall = (float) $data['rain']['3h'] / 3;
            }
        }

        if ($rainfall == 0 && in_array($weatherMain, ['Rain', 'Drizzle'])) {
            $rainfall = 0.5;
        }

        Log::info("🌧 Rainfall to be saved for village {$village->id}: $rainfall");

        // Save every reading (per minute)
        VillageWeatherHistory::create([
            'village_id' => $village->id,
            'date' => now()->toDateString(),
            'time' => now()->toTimeString(),
            'temperature' => $temperature,
            'rainfall' => $rainfall,
            'loss_flag' => false,
            'loss_reason' => null,
        ]);

        // Calculate total rainfall and average temperature for the day
        $today = now()->toDateString();
        $todayData = VillageWeatherHistory::where('village_id', $village->id)
            ->where('date', $today)
            ->get();

        if ($todayData->count() > 0) {
            $avgTemp = round($todayData->avg('temperature'), 2);
            $totalRain = round($todayData->sum('rainfall'), 2);

            VillageWeatherDailySummary::updateOrCreate(
                [
                    'village_id' => $village->id,
                    'date' => $today,
                ],
                [
                    'avg_temperature' => $avgTemp,
                    'avg_rainfall' => $totalRain,
                ]
            );
        }

        // Clean old minute-wise logs (after 14 days)
        VillageWeatherHistory::where('village_id', $village->id)
            ->where('date', '<', now()->subDays(14)->toDateString())
            ->delete();

        VillageWeatherDailySummary::where('village_id', $village->id)
            ->where('date', '<', now()->subDays(14)->toDateString())
            ->delete();
    }
}
