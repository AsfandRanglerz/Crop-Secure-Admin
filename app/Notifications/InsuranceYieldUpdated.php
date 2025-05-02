<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Support\Facades\Log;

class InsuranceYieldUpdated extends Notification
{
    protected $insuranceSubType;
    protected $insurance;
    protected $compensation;

    public function __construct($insuranceSubType, $insurance, $compensation)
    {
        $this->insuranceSubType = $insuranceSubType;
        $this->insurance = $insurance;
        $this->compensation = $compensation;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {

        return [
            'title' => 'Insurance Update for Crop: ' . $this->insurance->crop,
            'message' => $this->compensation > 0
                ? 'You are eligible for a compensation of Rs. ' . number_format($this->compensation)
                : 'No compensation is available as the yield met or exceeded your benchmark.',
            'compensation' => $this->compensation,
            'crop' => $this->insurance->crop,
            'year' => $this->insurance->year,
        ];
    }
}
