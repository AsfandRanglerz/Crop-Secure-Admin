<?php

namespace App\Jobs;

use App\Helpers\SimpleNotificationHelper;
use App\Models\Notification;
use App\Models\Farmer;
use App\Models\AuthorizedDealer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function handle()
    {
        $notification = $this->notification;
        $userTypes = explode(',', $notification->user_type);

        foreach ($userTypes as $type) {
            if ($type === 'farmer') {
                $recipients = Farmer::whereNotNull('fcm_token')->get();
            } elseif ($type === 'dealer') {
                $recipients = AuthorizedDealer::whereNotNull('fcm_token')->get();
            } else {
                continue;
            }

            foreach ($recipients as $recipient) {
                SimpleNotificationHelper::sendFcmNotification(
                    $recipient->fcm_token,
                    'Crop Secure Alert',
                    $notification->message,
                    ['notification_id' => (string)$notification->id]
                );
            }
        }

        $notification->update(['is_sent' => 1]);
    }
}
