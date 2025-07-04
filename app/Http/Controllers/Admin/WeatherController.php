<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Village;
use App\Models\VillageWeatherHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function fetchLast14DaysWeather($villageId)
    {
        $village = Village::find($villageId);
        if (!$village || !$village->latitude || !$village->longitude) return;

        $lat = $village->latitude;
        $lon = $village->longitude;
        $apiKey = '1e6e6235ad62763cd112c7a3adda011e';

        for ($i = 1; $i <= 14; $i++) {
            $date = Carbon::now()->subDays($i);
            $timestamp = $date->timestamp;

            $res = Http::get("https://api.openweathermap.org/data/2.5/onecall/timemachine", [
                'lat' => $lat,
                'lon' => $lon,
                'dt' => $timestamp,
                'units' => 'metric',
                'appid' => $apiKey,
            ]);

            if ($res->successful()) {
                $hourly = $res->json()['hourly'] ?? [];

                $avgTemp = collect($hourly)->avg('temp');
                $totalRain = collect($hourly)->sum(function ($hour) {
                    return $hour['rain']['1h'] ?? 0;
                });

                VillageWeatherHistory::updateOrCreate(
                    ['village_id' => $village->id, 'date' => $date->toDateString()],
                    ['temperature' => $avgTemp, 'rainfall' => $totalRain]
                );
            }
        }
    }
}
