<!DOCTYPE html>
<html>

<head>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h2>Monthly Gym Sales Report</h2>

    <table>
        <thead>
            <tr>
                <th>Plan Name</th>
                <th>Plan Price</th>
                <th>Plan Duration</th>
                <th>Allowed Entries</th>
                <th>Subscribers Count</th>
                <th>Total Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($salesData as $plan)
                <tr>
                    <td>{{ $plan->name }}</td>
                    <td>${{ number_format($plan->price, 2) }}</td>
                    <td>{{ $plan->duration }} days</td>
                    <td>{{ $plan->allowed_entries ?? 'Unlimited' }}</td>
                    <td>{{ $plan->subscription->count() }}</td>
                    <td>${{ number_format($plan->price * $plan->subscription->count(), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(isset($recentSubscriptions) && count($recentSubscriptions) > 0)
        <h3 style="margin-top: 40px;">Recent Subscriptions Revenue (Last Month)</h3>
        <table style="margin-top: 20px; width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #f2f2f2;">Plan Name</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #f2f2f2;">Subscriptions Count</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #f2f2f2;">Price per Subscription</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #f2f2f2;">Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Group subscriptions by plan and calculate totals
                    $planRevenues = [];
                    foreach($recentSubscriptions as $subscription) {
                        $planId = $subscription->membership_plan_id;
                        $planName = $subscription->membership_plan->name;
                        $planPrice = $subscription->membership_plan->price;
                        
                        if (!isset($planRevenues[$planId])) {
                            $planRevenues[$planId] = [
                                'name' => $planName,
                                'count' => 0,
                                'price' => $planPrice,
                                'total' => 0
                            ];
                        }
                        
                        $planRevenues[$planId]['count']++;
                        $planRevenues[$planId]['total'] += $planPrice;
                    }
                @endphp

                @foreach($planRevenues as $planId => $data)
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ $data['name'] }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $data['count'] }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">${{ number_format($data['price'], 2) }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold;">${{ number_format($data['total'], 2) }}</td>
                    </tr>
                @endforeach

                @php
                    // Calculate grand total
                    $grandTotal = array_sum(array_column($planRevenues, 'total'));
                    $totalSubscriptions = array_sum(array_column($planRevenues, 'count'));
                @endphp

                <tr style="font-weight: bold; background-color: #f8f9fa;">
                    <td style="border: 1px solid #ddd; padding: 8px;">Total</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $totalSubscriptions }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;"></td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">${{ number_format($grandTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #6c757d;">
            <p>No recent subscriptions found for the last month.</p>
        </div>
    @endif
</body>

</html>
