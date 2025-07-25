<?php

namespace App\Jobs;

use App\Helpers\NDVINotificationHelper;
use App\Models\Farmer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class SendNDVINotificationJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $farmer;
    protected $ndvi;
    protected $date;
    protected $insuranceTypeId;

    public function __construct(Farmer $farmer, $ndvi, $date, $insuranceTypeId)
    {
        $this->farmer = $farmer;
        $this->ndvi = $ndvi;
        $this->date = $date;
        $this->insuranceTypeId = $insuranceTypeId;
    }

    public function handle(): void
    {
        if ($this->farmer && $this->farmer->fcm_token) {
            NDVINotificationHelper::notifyFarmer(
                $this->farmer,
                $this->ndvi,
                $this->date,
                $this->insuranceTypeId
            );
        }
    }
}
