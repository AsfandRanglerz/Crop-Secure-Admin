<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeDealerMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // return $this->markdown('emails.welcomedealer')->subject('Welcome to Authorized Dealer Registration')
        // ->with(['data' => $this->user]);    

        return $this->view('emails.welcomedealer')
        ->subject('Welcome to Crop Secure')
        ->with(['data' => $this->user]);
    }
}
