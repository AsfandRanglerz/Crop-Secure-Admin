<?php

namespace App\Jobs;

use App\Models\Land;
use App\Models\InsuranceHistory;
use App\Models\InsuranceSubTypeSatelliteNDVI;
use App\Jobs\SendNDVINotificationJob;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class ProcessSatelliteNDVIJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $date, $landIds, $demarcationMap, $insuranceTypeId;
    protected $threshold = 0.4;
    protected $apiKey = 'apk.ec114200944764f1f5162bf2efc7cd4ccb9afb90efaa35594cf3058b0244d6da';

    public function __construct($date, array $landIds, array $demarcationMap, $insuranceTypeId)
    {
        $this->date = $date;
        $this->landIds = $landIds;
        $this->demarcationMap = $demarcationMap;
        $this->insuranceTypeId = $insuranceTypeId;
    }

    public function handle(): void
    {
        $notifiedHistories = [];

        foreach ($this->landIds as $landId) {
            $points = $this->demarcationMap[$landId] ?? null;
            if (!$points || count($points) < 3) continue;

            $coordinates = array_map(fn($point) => [(float) $point['longitude'], (float) $point['latitude']], $points);
            if ($coordinates[0] !== end($coordinates)) $coordinates[] = $coordinates[0];

            $response = Http::post("https://api-connect.eos.com/api/lms/search/v2/sentinel2?api_key={$this->apiKey}", [
                'search' => [
                    'date' => ['to' => $this->date],
                    'shape' => ['type' => 'Polygon', 'coordinates' => [$coordinates]],
                ]
            ]);

            if (!$response->ok()) continue;

            $viewId = $response->json()['results'][0]['view_id'] ?? null;
            if (!$viewId) continue;

            [$satellite, $utm_zone, $latitude_band, $grid_square, $year, $month, $day, $cloud] = explode('/', $viewId);
            $lat = array_sum(array_column($points, 'latitude')) / count($points);
            $lon = array_sum(array_column($points, 'longitude')) / count($points);

            $ndviResponse = Http::get("https://api-connect.eos.com/api/render/{$satellite}/point/{$utm_zone}/{$latitude_band}/{$grid_square}/{$year}/{$month}/{$day}/{$cloud}/NDVI/{$lat}/{$lon}?api_key={$this->apiKey}");
            if (!$ndviResponse->ok()) continue;

            $ndvi = $ndviResponse->json()['index_value'] ?? null;
            if (!is_numeric($ndvi)) continue;

            $exists = InsuranceSubTypeSatelliteNDVI::where('date', $this->date)
                ->where('land_id', $landId)
                ->where('insurance_type_id', $this->insuranceTypeId)
                ->exists();

            if ($exists) continue;

            InsuranceSubTypeSatelliteNDVI::create([
                'date' => $this->date,
                'land_id' => $landId,
                'ndvi' => $ndvi,
                'insurance_type_id' => $this->insuranceTypeId,
            ]);

            $farmerId = Land::where('id', $landId)->value('user_id');
            if (!$farmerId) continue;

            $farmers = InsuranceHistory::with('user')
                ->where('insurance_type_id', $this->insuranceTypeId)
                ->where('status', 'unclaimed')
                ->where('user_id', $farmerId)
                ->get();

            foreach ($farmers as $record) {
                $user = $record->user;
                if (!$user) continue;

                $uniqueKey = $user->id . '_' . $landId;
                if (in_array($uniqueKey, $notifiedHistories)) continue;

                $isLoss = $ndvi < $this->threshold;
                $comp = $isLoss ? $record->sum_insured : 0;

                $record->update([
                    'compensation_amount' => $comp,
                    'remaining_amount' => $comp,
                ]);

                if ($isLoss && $user->fcm_token) {
                    dispatch(new SendNDVINotificationJob($user, $ndvi, $this->date, $this->insuranceTypeId));
                }

                $notifiedHistories[] = $uniqueKey;
            }
        }
    }
}
