<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class CheckinController extends Controller
{
    public function processScan(string $payload)
    {
        $decryptedPayload = Crypt::decrypt($payload);
        dd($decryptedPayload);
    }
}
