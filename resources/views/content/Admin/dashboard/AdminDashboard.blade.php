@extends('layouts/AdminblankLayout')

@section('title', 'Dashboard - Analytics')

@section('vendor-style')
    @vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const subCountData = @json($Sub_count);

            const currentMonthData = Object.values(subCountData.this_month)
                .flatMap(week => Object.values(week));
            const lastMonthData = Object.values(subCountData.last_month)
                .flatMap(week => Object.values(week));
            console.log(lastMonthData);
            const days = Object.values(subCountData.this_month)
                .flatMap(week => Object.keys(week));

            const totalProfitLineChartEl = document.querySelector('#totalProfitLineChart');
            const totalProfitLineChartConfig = {
                chart: {
                    height: 350,
                    type: 'line',
                    zoom: {
                        enabled: false
                    },
                    toolbar: {
                        show: true
                    }
                },
                series: [{
                    name: 'Current Month',
                    data: currentMonthData
                }, {
                    name: 'Last Month',
                    data: lastMonthData
                }],
                xaxis: {

                    labels: {
                        show: false
                    }
                },
                yaxis: {
                    title: {
                        text: 'Number of Subscriptions'
                    }
                },
                colors: ['#ab2f2b', '#478d93'],
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                legend: {
                    position: 'top'
                },
                markers: {
                    size: 4,
                    hover: {
                        size: 6
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(value) {
                            return value + ' subscriptions';
                        }
                    }
                }
            };

            if (typeof totalProfitLineChartEl !== undefined && totalProfitLineChartEl !== null) {
                const totalProfitLineChart = new ApexCharts(totalProfitLineChartEl, totalProfitLineChartConfig);
                totalProfitLineChart.render();
            }
        });
    </script>
@endsection

@section('content')
    <div class="row gy-6">
        <!-- Congratulations card -->
        <div class="col-md-12 col-lg-4">
            <div class="card">
                <div class="card-body text-nowrap">
                    @if (!empty($bestSellingPlan))
                        <h5 class="card-title mb-0 flex-wrap text-nowrap">Best Selling Plan</h5>
                        <p class="mb-2">{{ $bestSellingPlan['subscriptions'] }} subscriptions</p>

                        <div class="best-sellers-list">
                            @foreach ($bestSellingPlan['plans'] as $plan)
                                <div class="best-seller-item mb-3">
                                    <h6 class="mb-1">{{ $plan->name }}</h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-label-primary">Top Seller</span>

                                        <img src="{{ asset('assets/img/illustrations/trophy.png') }}" width="20"
                                            alt="trophy" class="md-1">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-3">
                            <p class="mb-2">{{ round($bestSellingPlan['percentage'] * 100, 0) }}% of Total Subs</p>
                        </div>

                        <img src="{{ asset('assets/img/illustrations/trophy.png') }}"
                            class="position-absolute bottom-0 end-0 me-5 mb-5" width="83" alt="view sales">
                    @else
                        <div class="text-center py-4">
                            <h5 class="card-title mb-2 flex-wrap text-nowrap">No best seller</h5>
                            <p class="text-muted">No subscription data available yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Transactions -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="card-title m-0 me-2">Stats</h5>
                    </div>
                    @if ($sub_increase_percentage > 0)
                        <p class="small mb-0"><span class="h6 mb-0">{{ round($sub_increase_percentage * 100, 0) }}% Growth
                                in
                                Subs</span>
                            😎 this month</p>
                    @elseif ($sub_increase_percentage == 0)
                        <p class="small mb-0"><span class="h6 mb-0">No Change in Subs</span>
                            😔 this month</p>
                    @else
                        <p class="small mb-0"><span class="h6 mb-0">{{ round($sub_increase_percentage * -100, 0) }}%
                                Decrease
                                in
                                Subs</span>
                            😔 this month</p>
                    @endif
                </div>
                <div class="card-body pt-lg-10">
                    <div class="row g-6">
                        @if ($stat_counts['Members'] != 0)
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="avatar">
                                        <div class="avatar-initial bg-primary rounded shadow-xs">
                                            <i class="ri-group-line ri-24px"></i>
                                        </div>
                                    </div>

                                    <div class="ms-3">
                                        <p class="mb-0">Members</p>
                                        <h5 class="mb-0">{{ $stat_counts['Members'] }}</h5>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($stat_counts['MembershipPlans'] != 0)
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="avatar">
                                        <div class="avatar-initial bg-success rounded shadow-xs">
                                            <i class="ri-file-text-line ri-24px"></i>
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <p class="mb-0">Plans</p>
                                        <h5 class="mb-0">{{ $stat_counts['MembershipPlans'] }}</h5>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($stat_counts['CheckIns'] != 0)
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="avatar">
                                        <div class="avatar-initial bg-warning rounded shadow-xs">
                                            <i class="ri-macbook-line ri-24px"></i>
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <p class="mb-0">Total Check-ins</p>
                                        <h5 class="mb-0">{{ $stat_counts['CheckIns'] }}</h5>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body pt-lg-2">
            <div id="totalProfitLineChart" class="mb-3"></div>
            <div class="mt-1 mt-md-3">
                <div class="d-flex align-items-center gap-4">

                    <p class="small mb-0"><span class="h6 mb-0">This month's Revenue is
                            {{ round($revenue_percentage, 2) }}% of Last
                            month's Revenue</span>
                    </p>
                </div>
                <div class="d-grid mt-3 mt-md-4">
                    <button class="btn btn-primary" type="button">Details</button>
                </div>
            </div>
        </div>
    </div>
@endsection
