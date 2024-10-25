<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MembershipPlan;

class MembershipPlanController extends Controller
{
    //
    public function list()
    {
        $plans = MembershipPlan::all();
        return view('content.membership-plans.membership-plan', ['plans' => $plans]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {}
    public function __construct()
    {
        $this->middleware('auth');
    }
}
