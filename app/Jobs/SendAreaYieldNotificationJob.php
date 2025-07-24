<?php

namespace App\Jobs;

use App\Helpers\AreaYieldNotificationHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SendAreaYieldNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $cropName;
    protected $year;
    protected $currentYield;

    public function __construct($user, $cropName, $year, $currentYield)
    {
        $this->user = $user;
        $this->cropName = $cropName;
        $this->year = $year;
        $this->currentYield = $currentYield;
    }


    public function handle()
    {
        Log::info('⏳ SendAreaYieldNotificationJob started for user ID: ' . $this->user->id);

        try {
            AreaYieldNotificationHelper::notifyFarmer(
                $this->user,
                $this->cropName,
                $this->year,
                $this->currentYield
            );
            Log::info('✅ Notification sent to user ID: ' . $this->user->id);
        } catch (\Exception $e) {
            Log::error('❌ Failed to send notification to user ID: ' . $this->user->id . '. Error: ' . $e->getMessage());
        }
    }
}
