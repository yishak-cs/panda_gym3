<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QRcodeMailer extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Subscription $sub)
    {
        $this->sub = $sub;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $qrCodePath = storage_path('app/public/' . $this->sub->member->qrCode->path);

        return $this->view('content.email.qrcode')
            ->subject('Your QR Code')
            ->attach($qrCodePath, [
                'as' => 'your-qr-code.png',
                'mime' => 'image/png'
            ])
            ->with([
                'sub' => $this->sub,
                'name' => $this->sub->member->getName()
            ]);
    }
}
