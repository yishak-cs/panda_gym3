<?php

namespace App\Jobs;

use App\Mail\QRcodeMailer;
use App\Models\Subscription;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendQRCodeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(private Subscription $sub)
    {
        $this->sub = $sub;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->sub->member) {
                throw new \Exception('Member not found for subscription ID: ' . $this->sub->id);
            }

            if (!$this->sub->member->qrCode) {
                throw new \Exception('QR code not found for member ID: ' . $this->sub->member->id);
            }

            Mail::to($this->sub->member->email)->send(new QRcodeMailer($this->sub));

            Log::channel('email')->info('QR code email sent successfully', [
                'member_id' => $this->sub->member->id,
                'email' => $this->sub->member->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send QR code email', [
                'error' => $e->getMessage(),
                'subscription_id' => $this->sub->id
            ]);

            throw $e; // Rethrow to trigger job failure
        }
    }
}
