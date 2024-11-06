<?php

namespace App\Http\Controllers;


use App\Models\Subscription;
use App\Jobs\SendQRCodeEmail;
use Illuminate\Support\Facades\Log;


class MailController extends Controller
{
    //
    public static function sendQRcode(Subscription $sub)
    {
        // Dispatch the job to the queue
        try {
            SendQRCodeEmail::dispatch($sub);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send QR code email: ' . $e->getMessage());
            return false;
        }
    }
}
