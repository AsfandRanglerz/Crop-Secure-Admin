<?php

namespace App\Jobs;

use App\Models\InsuranceHistory;
use App\Models\InsuranceSubType;
use App\Helpers\ProductionPriceNotificationHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessProductionPriceNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $subType;
    protected $year;
    protected $insuranceTypeId;
    protected $cropNameId;
    protected $districtId;
    protected $tehsilId;

    public function __construct($subType, $year, $insuranceTypeId, $cropNameId, $districtId, $tehsilId)
    {
        $this->subType = $subType;
        $this->year = $year;
        $this->insuranceTypeId = $insuranceTypeId;
        $this->cropNameId = $cropNameId;
        $this->districtId = $districtId;
        $this->tehsilId = $tehsilId;
    }

    public function handle()
    {
        $farmers = InsuranceHistory::with('user')
            ->where('insurance_type_id', $this->insuranceTypeId)
            ->where('crop_id', $this->cropNameId)
            ->where('district_id', $this->districtId)
            ->where('tehsil_id', $this->tehsilId)
            ->whereYear('created_at', $this->year)
            ->get();

        foreach ($farmers as $record) {
            $user = $record->user;
            if (!$user) continue;

            $comp = 0;
            $lossStatus = 'no loss';

            if (
                $this->subType->cost_of_production !== null &&
                $this->subType->average_yield !== null &&
                $this->subType->real_time_market_price !== null &&
                $this->subType->ensured_yield !== null &&
                $record->benchmark !== null
            ) {
                $bep = $this->subType->cost_of_production / $this->subType->average_yield;
                $marketPrice = $this->subType->real_time_market_price;
                $ppi = ($marketPrice / $bep) * 100;
                $threshold = $record->benchmark;
                $triggerPrice = ($threshold / 100) * $bep;

                if ($ppi < $threshold && $marketPrice < $triggerPrice) {
                    $ensuredYield = $this->subType->ensured_yield; // ✅ Don't divide by 100
                    $area = $record->area ?? 1;

                    $comp = $ensuredYield * ($triggerPrice - $marketPrice) * $area;
                    $lossStatus = 'loss';

                    if ($user->fcm_token) {
                        ProductionPriceNotificationHelper::notifyFarmer(
                            $user,
                            $this->year,
                            $this->insuranceTypeId,
                            $this->districtId,
                            $this->tehsilId,
                            [
                                'ppi' => round($ppi, 2) . '%',
                                'trigger_price' => round($triggerPrice, 2),
                                'real_time_price' => $marketPrice,
                                'compensation' => round($comp, 2),
                            ]
                        );
                    }
                }
            }

            if ($comp > 0) {
                $record->update([
                    'compensation_amount' => round($comp, 2),
                    'remaining_amount' => round($comp, 2),
                ]);
            }
        }
    }
}
