<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailerService extends Mailable
{
    use Queueable, SerializesModels;

    protected $salesData;

    /**
     * Create a new message instance.
     */
    public function __construct($salesData)
    {
        $this->salesData = $salesData;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Monthly Gym Sales Report')
            ->view('emails.monthly')
            ->with([
                'salesData' => $this->salesData
            ]);
    }
}
