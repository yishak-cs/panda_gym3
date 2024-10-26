<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MembershipPlan;
use Illuminate\Validation\Rule;

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
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'string|required|unique:\App\Models\MembershipPlan,name',
            'duration' => 'integer|required',
            'price' => 'integer|required',
            'allowed_entries' => 'integer|nullable',
            'description' => 'string'
        ]);

        $plan = new MembershipPlan($request->all());

        if ($plan->save()) {
            return redirect()->back()->with('success', 'Plan added successfully!');
        }
        return redirect()->back()->with('error', 'Failed to add Plan!');
    }

    public function edit(MembershipPlan $plan, Request $request)
    {
        /**
         * check updating flag
         *
         * @var bool $flag
         */
        $flag = false;

        if ($request->get('name') != $plan->name) {
            $flag = true;
        }
        if ($request->get('duration') != $plan->duration) {
            $flag = true;
        }
        if ($request->get('allowed_entries' != $plan->allowed_entries)) {
            $flag = true;
        }
        if (!$flag) {
            return redirect()->back()->with('success', 'Nothing to Update');
        }

        $request->validate([
            'name' => ['string', 'nullable', Rule::unique('membership_plans', 'name')->ignore($plan->id),],
            'duration' => 'integer|required',
            'allowed_entries' => 'integer|nullable',
        ]);

        if ($plan->update([
            'name' => $request->get('name'),
            'duration' => $request->get('duration'),
            'allowed_entries' => $request->get('allowed_entries')
        ])) {
            return redirect()->back()->with('success', 'Plan updated Successfully');
        }
        return redirect()->back()->with('error', 'Something went Wrong');
    }
    /**
     * delete a resource in storage.
     *
     * @param  app\Models\MembershipPlan  $plan
     * @return \Illuminate\Http\Response
     */
    public function destroy(MembershipPlan $plan)
    {
        if ($plan->delete()) {
            return redirect()->back()->with('success', 'Plan deleted successfully!');
        }
        return redirect()->back()->with('error', 'Something went wrong!');
    }


    public function __construct()
    {
        $this->middleware('auth');
    }
}
