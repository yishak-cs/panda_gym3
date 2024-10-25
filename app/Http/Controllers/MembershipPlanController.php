<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MembershipPlanController extends Controller
{
    //
    public function add()
    {
        return view('content.membership-plans.add-membership-plan');
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
