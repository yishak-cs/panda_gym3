<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailerService extends Mailable
{
    use Queueable, SerializesModels;

    protected $salesData;
    protected $recentSubscriptions;

    /**
     * Create a new message instance.
     */
    public function __construct($salesData, $recentSubscriptions)
    {
        $this->salesData = $salesData;
        $this->recentSubscriptions = $recentSubscriptions;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Monthly Gym Sales Report')
            ->view('emails.monthly')
            ->with([
                'salesData' => $this->salesData,
                'recentSubscriptions' => $this->recentSubscriptions
            ]);
    }
}
