@extends('layouts/AdminblankLayout')

@section('title', 'Membership Plans - Revenue')

@section('vendor-style')
    @vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/v/dt/dt-2.1.8/fh-4.0.1/r-3.0.3/sc-2.4.3/sb-1.8.1/sp-2.3.3/datatables.min.css"
        rel="stylesheet">
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('content')
    <div class="row">
        <div class="card mb-6 col-xl">
            <div class="container-xxl flex-grow-1 container-p-y">

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif


                <div class="table-responsive text-nowrap">
                    <!-- DataTable with Buttons -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Membership Plans</h5>
                            <button class="btn btn-link" id="toggleTable">
                                <i class="ri-arrow-down-s-line"></i>
                            </button>
                        </div>
                        <div class="card-datatable table-responsive" id="tableContainer" style="display: none;">
                            <table class="datatables-members table border-top" id="membersTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Duration</th>
                                        <th>Price</th>
                                        <th>Allowed Entries</th>
                                        <th>Sub Count</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($membership as $plan)
                                        <tr>
                                            <td>{{ $plan->id }}</td>
                                            <td>{{ $plan->name }}</td>
                                            <td>{{ $plan->duration }}</td>
                                            <td>{{ $plan->price }}</td>
                                            <td>{{ $plan->allowed_entries == null ? 'Unlimited' : $plan->allowed_entries }}
                                            </td>
                                            <td>{{ $revenue[$plan->name]['sub_count'] }}</td>
                                            <td>{{ $revenue[$plan->name]['sub_count'] * $revenue[$plan->name]['price'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!--/ DataTable with Buttons -->
            </div>
        </div>
    </div>
    <div class="row gy-6">
        <!-- yearly Overview Chart -->
        <div class="card h-full col-xl-4 col-md-6">

            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <h5 class="mb-1">Yearly Revenue Overview</h5>
                </div>
            </div>

            <div class="card-body pt-lg-2">
                <div id="RevenueReportChart"></div>
                <div class="mt-1 mt-md-3">
                    <div class="d-flex align-items-center gap-4">
                        <p class="small mb-0"><span class="h6 mb-0">This month's Revenue is
                                {{ round($revenue_percentage, 2) }}%
                                of
                                Last
                                month's Revenue</span>
                        </p>
                    </div>
                </div>
            </div>

        </div>
        <!-- End of yearly Overview Chart -->
        <!-- checkintimes bubble chart -->
        <div class="col-xl-4 col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h5 class="mb-1">Check-ins Time Distribution</h5>
                    </div>
                </div>

                <div class="card-body pt-lg-2">
                    <div id="CheckinsReportChart"></div>
                    <div class="mt-1 mt-md-3">
                        <div class="d-flex align-items-center gap-4">
                            <p class="small mb-0"><span class="h6 mb-0">This month's Checkins is {{ $checkinPercentage }}%
                                    of
                                    Last
                                    month's Checkins</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end of checkintimes bubble chart -->

        <!-- Membership Revenue contribution -->
        <div class="card col-xl-4 col-md-6">
            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <h5 class="mb-1">Yearly Revenue Overview</h5>
                </div>
            </div>

            <div class="card-body pt-lg-4">
                <div id="chart"></div>
            </div>
        </div>
        <!-- End Membership Revenue contribution-->

    </div>
@endsection

@section('page-script')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <script src="https://cdn.datatables.net/v/dt/dt-2.1.8/fh-4.0.1/r-3.0.3/sc-2.4.3/sb-1.8.1/sp-2.3.3/datatables.min.js">
    </script>
    <script>
        document.getElementById('toggleTable').addEventListener('click', function() {
            const tableContainer = document.getElementById('tableContainer');
            tableContainer.style.display = tableContainer.style.display === 'none' ? 'block' : 'none';
        });
    </script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = new DataTable('#membersTable');
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartBgColor = '#f8f9fa';
            const borderColor = '#f0f0f0';
            const labelColor = '#6c757d';
            const primaryColor = '#696cff';

            const RevenueReportChartEl = document.querySelector('#RevenueReportChart');
            const RevenueReportChartConfig = {
                chart: {
                    type: 'bar',
                    height: 300,
                    offsetY: -9,
                    offsetX: -16,
                    parentHeightOffset: 0,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Sales',
                    data: [
                        {{ $monthlyRevenue['Jan'] }},
                        {{ $monthlyRevenue['Feb'] }},
                        {{ $monthlyRevenue['Mar'] }},
                        {{ $monthlyRevenue['Apr'] }},
                        {{ $monthlyRevenue['May'] }},
                        {{ $monthlyRevenue['Jun'] }},
                        {{ $monthlyRevenue['Jul'] }},
                        {{ $monthlyRevenue['Aug'] }},
                        {{ $monthlyRevenue['Sep'] }},
                        {{ $monthlyRevenue['Oct'] }},
                        {{ $monthlyRevenue['Nov'] }},
                        {{ $monthlyRevenue['Dec'] }}
                    ]
                }],
                colors: ['#ab2f2b'],
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        columnWidth: '30%',
                        endingShape: 'rounded',
                        startingShape: 'rounded',
                        colors: {
                            ranges: {
                                color: primaryColor
                            }

                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                legend: {
                    show: false
                },
                grid: {
                    strokeDashArray: 8,
                    borderColor,
                    padding: {
                        bottom: -10
                    }
                },
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',
                        'Dec'
                    ],
                    tickPlacement: 'on',
                    labels: {
                        show: false
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    crosshairs: {
                        opacity: 0
                    }
                },
                yaxis: {
                    show: true,
                    tickAmount: 3,
                    labels: {
                        formatter: function(val) {
                            return parseInt(val) + ' birr';
                        },
                        style: {
                            fontSize: '13px',
                            fontFamily: 'Inter',
                            colors: labelColor
                        }
                    }
                },
                states: {
                    hover: {
                        filter: {
                            type: 'none'
                        }
                    },
                    active: {
                        filter: {
                            type: 'none'
                        }
                    }
                },
                responsive: [{
                        breakpoint: 1500,
                        options: {
                            plotOptions: {
                                bar: {
                                    columnWidth: '40%'
                                }
                            }
                        }
                    },
                    {
                        breakpoint: 1200,
                        options: {
                            plotOptions: {
                                bar: {
                                    columnWidth: '30%'
                                }
                            }
                        }
                    },
                    {
                        breakpoint: 815,
                        options: {
                            plotOptions: {
                                bar: {
                                    borderRadius: 5
                                }
                            }
                        }
                    },
                    {
                        breakpoint: 768,
                        options: {
                            plotOptions: {
                                bar: {
                                    borderRadius: 10,
                                    columnWidth: '20%'
                                }
                            }
                        }
                    },
                    {
                        breakpoint: 568,
                        options: {
                            plotOptions: {
                                bar: {
                                    borderRadius: 8,
                                    columnWidth: '30%'
                                }
                            }
                        }
                    },
                    {
                        breakpoint: 410,
                        options: {
                            plotOptions: {
                                bar: {
                                    columnWidth: '50%'
                                }
                            }
                        }
                    }
                ]
            };

            if (typeof RevenueReportChartEl !== undefined && RevenueReportChartEl !== null) {
                const RevenueReportChart = new ApexCharts(RevenueReportChartEl, RevenueReportChartConfig);
                RevenueReportChart.render();
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Assuming HourData is passed to the view as a JSON object
            var hourData = @json($HourData);

            var seriesData = Object.keys(hourData).map(function(hour) {
                return {
                    x: hour,
                    y: hourData[hour],
                    z: hourData[hour] // You can adjust 'z' to represent another dimension if needed
                };
            });

            var options = {
                series: [{
                    name: 'Check-ins',
                    data: seriesData
                }],
                chart: {
                    height: 300,
                    type: 'bubble',
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                fill: {
                    opacity: 0.8
                },
                xaxis: {
                    tickAmount: 19, // Number of hours from 5 to 23
                    type: 'category',
                    categories: Object.keys(hourData) // Use hour keys as categories
                },
                yaxis: {
                    max: Math.max(...Object.values(hourData)) + 5 // Adjust max based on data
                }
            };

            var chart = new ApexCharts(document.querySelector("#CheckinsReportChart"), options);
            chart.render();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Calculate total revenue and percentages
            const revenue = @json($revenue);
            let totalRevenue = 0;
            let contributionData = [];

            // Calculate total revenue
            Object.entries(revenue).forEach(([planName, data]) => {
                totalRevenue += data.sub_count * data.price;
            });

            // Calculate percentage contributions
            Object.entries(revenue).forEach(([planName, data]) => {
                const planRevenue = data.sub_count * data.price;
                const contribution = (planRevenue / totalRevenue) * 100;
                contributionData.push({
                    name: planName,
                    value: parseFloat(contribution.toFixed(2))
                });
            });

            // Chart configuration
            var options = {
                series: contributionData.map(item => item.value),
                labels: contributionData.map(item => item.name),
                chart: {
                    height: 400,
                    type: 'donut',
                },
                plotOptions: {
                    pie: {
                        startAngle: -90,
                        endAngle: 270
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return val.toFixed(1) + '%';
                    }
                },
                fill: {
                    type: 'gradient',
                },
                legend: {
                    formatter: function(val, opts) {
                        return val + " - " + opts.w.globals.series[opts.seriesIndex] + '%';
                    }
                },
                title: {
                    text: 'Membership Plan Revenue Distribution',
                    align: 'center'
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 300
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            var chart = new ApexCharts(document.querySelector("#chart"), options);
            chart.render();
        });
    </script>
@endsection
