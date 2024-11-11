<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Members;
use App\Models\CheckIns;
use App\Models\CheckInTimes;
use App\Models\Subscription;
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
        // Calculate the percentage of current month's revenue compared to last month
        $sub_increase_percentage = $last_month_subs > 0
            ? ($this_month_subs - $last_month_subs) / $last_month_subs
            : ($this_month_subs == 0 && $last_month_subs == 0 ? 0 : 1);
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
        // Calculate last month's total revenue
        $last_month_revenue = Subscription::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->with('membership_plan')
            ->get()
            ->sum(function ($subscription) {
                return $subscription->membership_plan->price;
            });

        // Calculate current month's revenue so far
        $current_month_revenue = Subscription::whereMonth('created_at', Carbon::now()->month)
            ->with('membership_plan')
            ->get()
            ->sum(function ($subscription) {
                return $subscription->membership_plan->price;
            });

        // Calculate the percentage of current month's revenue compared to last month
        $revenue_percentage = $last_month_revenue > 0
            ? ($current_month_revenue / $last_month_revenue) * 100
            : 100;

        return view('content.dashboard.AdminDashboard', [
            'bestSellingPlan' => $bestSellingPlan,
            'stat_counts' => $stat_counts,
            'sub_increase_percentage' => $sub_increase_percentage,
            'Sub_count' => $Sub_count,
            'revenue_percentage' => $revenue_percentage
        ]);
    }

    public function settings()
    {
        return view('content.dashboard.AdminDashboard', []);
    }

    public function revenue()
    {
        $revenue = [];
        $membership_plan = MembershipPlan::with('subscription')->get();
        foreach ($membership_plan as $plan) {
            $revenue[$plan->name] = count(Subscription::where('membership_plan_id', $plan->id)->get());
        }

        // Initialize an associative array for monthly revenue
        $monthlyRevenue = [
            'Jan' => 0,
            'Feb' => 0,
            'Mar' => 0,
            'Apr' => 0,
            'May' => 0,
            'Jun' => 0,
            'Jul' => 0,
            'Aug' => 0,
            'Sep' => 0,
            'Oct' => 0,
            'Nov' => 0,
            'Dec' => 0
        ];

        // Fetch all subscriptions for the current year
        $yearlyData = Subscription::whereYear('created_at', Carbon::now()->year)->with('membership_plan')->get();

        // Calculate revenue for each month
        foreach ($yearlyData as $subscription) {
            $month = $subscription->created_at->format('M');
            $monthlyRevenue[$month] += $subscription->membership_plan->price;
        }

        // Calculate last month's total revenue
        $last_month_revenue = Subscription::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->with('membership_plan')
            ->get()
            ->sum(function ($subscription) {
                return $subscription->membership_plan->price;
            });

        // Calculate current month's revenue so far
        $current_month_revenue = Subscription::whereMonth('created_at', Carbon::now()->month)
            ->with('membership_plan')
            ->get()
            ->sum(function ($subscription) {
                return $subscription->membership_plan->price;
            });

        // Calculate the percentage of current month's revenue compared to last month
        $revenue_percentage = $last_month_revenue > 0
            ? ($current_month_revenue / $last_month_revenue) * 100
            : 100;
        $HourData = array_fill(5, 19, 0); // Initialize hours 5 to 23 with zero counts

        $checkin_times = CheckInTimes::get();

        foreach ($checkin_times as $checkin_time) {
            $hour = $checkin_time->created_at->hour;
            $minute = $checkin_time->created_at->minute;

            // Determine the hour bucket based on minutes
            if ($minute >= 31) {
                $hour += 1;
            }

            // Only count hours between 5 and 23
            if ($hour >= 5 && $hour <= 23) {
                $HourData[$hour]++;
            }
        }

        return view('content.settings.settings', [
            'membership' => $membership_plan,
            'revenue' => $revenue,
            'monthlyRevenue' => $monthlyRevenue, // Pass monthly revenue to the view
            'revenue_percentage' => $revenue_percentage,
            'HourData' => $HourData
        ]);
    }
}

/**  $revenue = 0;
 *   foreach (Subscription::where()->get() as $sub) {
 *   $revenue += $sub->membership_plan->price;
 *         } 
 */
