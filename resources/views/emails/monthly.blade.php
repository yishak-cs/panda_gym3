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
</body>

</html>
