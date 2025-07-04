<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Village;
use App\Models\VillageWeatherHistory;
use Carbon\Carbon;

class WeatherController extends Controller
{
    public function getCurrentWeather()
    {
        $apiKey = '1e6e6235ad62763cd112c7a3adda011e';

        $villages = Village::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $results = [];

        foreach ($villages as $village) {
            $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
                'lat' => $village->latitude,
                'lon' => $village->longitude,
                'units' => 'metric',
                'appid' => $apiKey,
            ]);

            if ($response->successful()) {
                $results[] = [
                    'village_id' => $village->id,
                    'village_name' => $village->name ?? 'Unknown',
                    'weather' => $response->json(),
                ];
            } else {
                $results[] = [
                    'village_id' => $village->id,
                    'village_name' => $village->name ?? 'Unknown',
                    'error' => $response->json('message') ?? 'Failed to fetch',
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    


}
