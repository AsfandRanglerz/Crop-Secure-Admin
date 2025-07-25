<?php

namespace App\Jobs;

use App\Models\Farmer;
use App\Helpers\SimpleNotificationHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendFarmerNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $farmerId;
    protected $title;
    protected $message;

    public function __construct($farmerId, $title, $message)
    {
        $this->farmerId = $farmerId;
        $this->title = $title;
        $this->message = $message;
    }

    public function handle()
    {
        $farmer = Farmer::find($this->farmerId);

        if ($farmer && $farmer->fcm_token) {
            $cleanToken = trim($farmer->fcm_token);

            if (strlen($cleanToken) < 20) {
                Log::warning("Skipped FCM for Farmer ID {$this->farmerId}: Token too short or invalid.");
                return;
            }

            try {
                SimpleNotificationHelper::sendFcmNotification(
                    $cleanToken,
                    $this->title,
                    $this->message
                );
            } catch (\Exception $e) {
                Log::error("FCM send failed for Farmer ID {$this->farmerId}: " . $e->getMessage());
            }
        } else {
            Log::warning("Farmer ID {$this->farmerId} has no FCM token.");
        }
    }
}
