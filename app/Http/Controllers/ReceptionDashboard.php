<?php

namespace App\Http\Controllers;

use App\Models\MembershipPlan;

class ReceptionDashboard extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plans = MembershipPlan::all();
        return view('content.Receptionist.dashboard.ReceptionDashboard', compact('plans'));
    }
}
