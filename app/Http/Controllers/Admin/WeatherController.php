<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Village;
use App\Models\VillageWeatherHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $rainfall = $data['rain']['1h'] ?? 0;
        $weatherMain = $data['weather'][0]['main'] ?? '';

        // If raining but rain[1h] missing, assume light rain
        if ($rainfall == 0 && in_array($weatherMain, ['Rain', 'Drizzle'])) {
            $rainfall = 0.5;
        }

        $crop = $village->villageCrops()->first();
        $expectedTemp = $crop->avg_temp ?? 30;
        $expectedRain = $crop->avg_rainfall ?? 20;

        $lossFlag = false;
        $lossReason = null;

        if ($temperature > $expectedTemp + 5) {
            $lossFlag = true;
            $lossReason = 'High temperature';
        } elseif ($rainfall < $expectedRain * 0.5 || $rainfall > $expectedRain * 1.5) {
            $lossFlag = true;
            $lossReason = 'Abnormal rainfall';
        }

        // Save to history
        VillageWeatherHistory::create([
            'village_id' => $village->id,
            'date' => now()->toDateString(),
            'time' => now()->toTimeString(),
            'temperature' => $temperature,
            'rainfall' => $rainfall,
            'loss_flag' => $lossFlag,
            'loss_reason' => $lossReason,
        ]);

        // Keep only last 14 days of non-loss data, preserve loss entries
        VillageWeatherHistory::where('village_id', $village->id)
            ->where('date', '<', now()->subDays(14)->toDateString())
            ->where('loss_flag', false)
            ->delete();
    }
}
