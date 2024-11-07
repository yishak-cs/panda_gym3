<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class CheckinController extends Controller
{
    public function processScan(string $member_id, string $subscription_id, string $membership_name, string $end_date, string $timestamp)
    {
        dd($member_id, $subscription_id, $membership_name, $end_date, $timestamp);
    }
}
