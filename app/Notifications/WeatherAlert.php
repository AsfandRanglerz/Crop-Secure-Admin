<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class WeatherAlert extends Notification
{
   use Queueable;

    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Weather Alert',
            'message' => $this->message,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Weather Alert',
            'message' => $this->message,
        ]);
    }
}
