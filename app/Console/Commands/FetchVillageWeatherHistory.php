<?php

namespace App\Console\Commands;

use App\Models\CropInsurance;
use App\Models\Farmer;
use App\Models\Village;
use App\Models\VillageWeatherHistory;
use App\Notifications\WeatherAlert;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class FetchVillageWeatherHistory extends Command
{
    protected $signature = 'weather:fetch-history';
    protected $description = 'Fetch 14-day weather for each village and notify farmers on critical conditions';

    public function handle()
    {
        $villages = Village::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $apiKey = '1e6e6235ad62763cd112c7a3adda011e';

        foreach ($villages as $village) {
            for ($i = 0; $i < 14; $i++) {
                $date = Carbon::now()->subDays($i);
                $timestamp = $date->timestamp;

                $res = Http::get('https://api.openweathermap.org/data/2.5/onecall/timemachine', [
                    'lat' => $village->latitude,
                    'lon' => $village->longitude,
                    'dt' => $timestamp,
                    'units' => 'metric',
                    'appid' => $apiKey,
                ]);

                if ($res->successful()) {
                    $hourly = $res->json('hourly') ?? [];

                    $avgTemp = collect($hourly)->avg('temp');
                    $totalRain = collect($hourly)->sum(fn($h) => $h['rain']['1h'] ?? 0);

                    VillageWeatherHistory::updateOrCreate(
                        ['village_id' => $village->id, 'date' => $date->toDateString()],
                        ['temperature' => $avgTemp, 'rainfall' => $totalRain]
                    );
                }
            }

            // Get admin-set averages from village_crops
            $cropData = $village->villageCrops()->first();
            if (!$cropData) continue;

            $avgTemp = $cropData->avg_temp;
            $avgRain = $cropData->avg_rainfall;

            // High temperature check (14-day streak)
            $highTempDays = VillageWeatherHistory::where('village_id', $village->id)
                ->where('date', '>=', Carbon::now()->subDays(13)->toDateString())
                ->where('temperature', '>=', $avgTemp * 1.2)
                ->count();

            // Total rainfall of last 14 days
            $rainfall = VillageWeatherHistory::where('village_id', $village->id)
                ->where('date', '>=', Carbon::now()->subDays(13)->toDateString())
                ->sum('rainfall');

            // Get farmers via crop_insurances where village_id matches
            $farmerIds = CropInsurance::where('village_id', $village->id)->pluck('user_id')->unique();
            $farmers = Farmer::whereIn('id', $farmerIds)
                ->whereNotNull('fcm_token')
                ->get();

            // Notify farmers if temperature is too high for 14 days
            if ($highTempDays === 14) {
                foreach ($farmers as $farmer) {
                    $this->sendFCMNotification(
                        $farmer->fcm_token,
                        'Your village had 14 days of high temperature above normal.'
                    );
                }
            }

            // Notify farmers if rainfall is 50% more/less than average
            if ($rainfall >= $avgRain * 1.5 || $rainfall <= $avgRain * 0.5) {
                foreach ($farmers as $farmer) {
                    $this->sendFCMNotification(
                        $farmer->fcm_token,
                        'Rainfall in your village is 50% more or less than normal.'
                    );
                }
            }
        }

        $this->info('✅ Village weather history updated and farmer notifications sent.');
    }

    protected function sendFCMNotification($token, $message)
    {
        $SERVER_API_KEY = 'your_fcm_server_key'; 

        $data = [
            "to" => $token,
            "notification" => [
                "title" => "🌾 Weather Alert",
                "body" => $message,
                "sound" => "default"
            ]
        ];

        $headers = [ 
            'Authorization' => "key=$SERVER_API_KEY",
            'Content-Type' => 'application/json',
        ];

        $res = Http::withHeaders($headers)->post('https://fcm.googleapis.com/fcm/send', $data);

        if ($res->failed()) {
            Log::error('❌ FCM failed for token ' . $token . ': ' . $res->body());
        }
    }
}