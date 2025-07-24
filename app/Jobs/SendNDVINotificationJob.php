<?php

namespace App\Jobs;

use App\Helpers\NDVINotificationHelper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class SendNDVINotificationJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $ndvi;
    protected $date;
    protected $insuranceTypeId;

    public function __construct(User $user, $ndvi, $date, $insuranceTypeId)
    {
        $this->user = $user;
        $this->ndvi = $ndvi;
        $this->date = $date;
        $this->insuranceTypeId = $insuranceTypeId;
    }

    public function handle(): void
    {
        if ($this->user && $this->user->fcm_token) {
            NDVINotificationHelper::notifyFarmer(
                $this->user,
                $this->ndvi,
                $this->date,
                $this->insuranceTypeId
            );
        }
    }
}
