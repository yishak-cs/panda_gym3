<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Members;
use App\Models\CheckIns;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\MembershipPlan;

class AdminDashboard extends Controller
{
    //
    public function index()
    {
        $members_count = Members::count();
        $checkins_count = CheckIns::count();
        $membership_count = MembershipPlan::count();
        $stat_counts = [
            'MembershipPlans' => $membership_count,
            'Members' => $members_count,
            'CheckIns' => $checkins_count,
        ];

        // Get plans with their subscription counts, ordered by count
        $bestSellingPlans = MembershipPlan::withCount('subscription')
            ->having('subscription_count', '>', 0)
            ->orderByDesc('subscription_count')
            ->get();

        $bestSellingPlan = null;
        if ($bestSellingPlans->isNotEmpty()) {
            // Get the highest subscription count
            $maxSubscriptions = $bestSellingPlans->first()->subscription_count;

            // Get all plans that have the same highest count
            $bestSellingPlan = [
                'percentage' => $maxSubscriptions / Subscription::count(),
                'subscriptions' => $maxSubscriptions,
                'plans' => $bestSellingPlans->filter(function ($plan) use ($maxSubscriptions) {
                    return $plan->subscription_count === $maxSubscriptions;
                })
            ];
        }
        $this_month_subs = Subscription::whereMonth('created_at', Carbon::now()->month)->count();
        $last_month_subs = Subscription::whereMonth('created_at', Carbon::now()->subMonth()->month)->count();
        if ($this_month_subs == 0 && $last_month_subs == 0) {
            $sub_increase_percentage = 0;
        } elseif ($last_month_subs == 0) {
            $sub_increase_percentage = 1;
        } else {
            $sub_increase_percentage = ($this_month_subs - $last_month_subs) / $last_month_subs;
        }
        return view('content.dashboard.AdminDashboard', [
            'bestSellingPlan' => $bestSellingPlan,
            'stat_counts' => $stat_counts,
            'sub_increase_percentage' => $sub_increase_percentage
        ]);
    }

    public function settings()
    {
        /**
         * week dates
         *
         * @var array<string> $week_dates
         */
        $month_dates = [
            'week_1' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'week_2' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'week_3' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'week_4' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        ];

        /**
         * get current month
         *
         * @var \Carbon\Carbon $current_month
         */
        $current_month = Carbon::now()->setTimezone('Africa/Addis_Ababa');

        /**
         * get last month
         *
         * @var \Carbon\Carbon $last_month
         */
        $last_month = Carbon::now()->subMonth()->setTimezone('Africa/Addis_Ababa');
        $Sub_count = [
            'last_month' => [],
            'this_month' => []
        ];

        // Process each week
        foreach ($month_dates as $week_num => $days) {
            // Calculate the starting day for this week (0 for week 1, 7 for week 2, etc.)
            $week_offset = (int)substr($week_num, -1) - 1;
            $start_day = $week_offset * 7;

            // Process each day in the week
            foreach ($days as $day_index => $day_name) {
                $current_day = $start_day + $day_index;

                // Process current month
                if ($current_month->startOfMonth()->addDays($current_day)->isPast()) {
                    $Sub_count['this_month'][$week_num][$day_name] = count(
                        Subscription::whereDate(
                            'created_at',
                            $current_month->startOfMonth()->addDays($current_day)->format('Y-m-d H:i:s')
                        )->get()
                    );
                }

                // Process last month
                $Sub_count['last_month'][$week_num][$day_name] = count(
                    Subscription::whereDate(
                        'created_at',
                        $last_month->startOfMonth()->addDays($current_day)->format('Y-m-d H:i:s')
                    )->get()
                );
            }
        }

        $revenue = 0;
        foreach (Subscription::where()->get() as $sub) {
            $revenue += $sub->membership_plan->price;
        }
        return view('content.dashboard.AdminSettings');
    }
}
