<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Village;
use App\Models\VillageWeatherHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        // ✅ Current weather endpoint
        $res = Http::get("https://api.openweathermap.org/data/2.5/weather", [
            'lat' => $lat,
            'lon' => $lon,
            'units' => 'metric',
            'appid' => $apiKey,
        ]);

        if ($res->successful()) {
            $data = $res->json();
            $avgTemp = $data['main']['temp'] ?? null;
            $totalRain = $data['rain']['1h'] ?? 0;

            // ✅ Store today's data
            \App\Models\VillageWeatherHistory::updateOrCreate(
                ['village_id' => $village->id, 'date' => now()->toDateString()],
                ['temperature' => $avgTemp, 'rainfall' => $totalRain]
            );

            // ✅ Delete old data (15+ days)
            \App\Models\VillageWeatherHistory::where('village_id', $village->id)
                ->where('date', '<', now()->subDays(14)->toDateString())
                ->delete();
        }
    }
}
