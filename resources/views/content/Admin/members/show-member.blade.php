@extends('layouts/AdminblankLayout')
@section('title', 'Member Profile')
@section('page-style')
    <style>
        .checkin-calendar {
            display: flex;
            flex-flow: row wrap;
            gap: 3px;
            align-items: flex-start;
        }

        .calendar-day {
            width: 15px;
            height: 15px;
            border-radius: 2px;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .calendar-day:hover {
            opacity: 0.8;
        }

        /* Tooltip custom styles */
        .tooltip-header {
            font-weight: bold;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 0.25rem;
            padding-bottom: 0.25rem;
        }

        .tooltip-times {
            padding-top: 0.25rem;
        }

        .tooltip-time {
            padding-left: 0.5rem;
            white-space: nowrap;
        }
    </style>
@endsection

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Member Profile Card -->
        <div class="card shadow-lg">
            <!-- Header Section -->
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h3 text-white mb-0">{{ $member->getName() }}</h2>
                        <p class="mb-0">Member since {{ \Carbon\Carbon::parse($subscription->startDate)->format('M Y') }}
                        </p>
                    </div>
                    <!-- QR Code Modal Trigger -->
                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="modal"
                        data-bs-target="#qrCodeModal">
                        <i class="bi bi-qr-code me-1"></i> View QR Code
                    </button>
                </div>
            </div>
            <br>
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
            <!-- Member Information Grid -->
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h3 class="h5">Personal Information</h3>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Email:</strong> {{ $member->email }}</li>
                            <li class="list-group-item"><strong>Phone:</strong> {{ $member->phone_number }}</li>
                            <li class="list-group-item"><strong>Gender:</strong> {{ ucfirst($member->sex) }}</li>
                        </ul>
                    </div>
                    <!-- Fitness Goals -->
                    <div class="col-md-6 mb-4">
                        <h3 class="h5">Fitness Goals</h3>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Goal:</strong> {{ $member->goal }}</li>
                            <li class="list-group-item"><strong>Current Weight:</strong> {{ $member->current_weight }} kg
                            </li>
                            <li class="list-group-item"><strong>Target Weight:</strong> {{ $member->target_weight }} kg</li>
                            @if (!is_null($member->length))
                                <li class="list-group-item"><strong>BMI </strong>
                                    {{ round($member->current_weight / pow($member->length, 2), 2) }} kg/m<sup>2</sup></li>
                            @endif
                        </ul>
                    </div>
                </div>
                <!-- Membership Status -->
                <div class="mt-4">
                    <h3 class="h5">Membership Status</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Plan name:</strong>
                                {{ $subscription->membership_plan->name }}
                            </p>
                            <p><strong>Entries Left:</strong>
                                {{ is_null($subscription->membership_plan->allowed_entries) ? '∞' : $subscription->membership_plan->allowed_entries - $count_check }}
                            </p>
                            <p><strong>Plan Expiry:</strong>
                                {{ \Carbon\Carbon::parse($subscription->endDate)->format('M d, Y') }}
                                ({{ \Carbon\Carbon::parse($subscription->endDate)->diffForHumans() }})</p>
                        </div>
                    </div>
                </div>
                <!-- Check-in History -->
                {{-- checkin-calendar.blade.php --}}
                @php
                    use Carbon\Carbon;
                    use Carbon\CarbonPeriod;

                    // Create a period between start and end date
                    $period = CarbonPeriod::create($subscription->startDate, $subscription->endDate);

                    // Just collect all days without week grouping
                    $days = collect($period);
                @endphp

                <div class="card mt-4 {{ $subscription->endDate->isPast() ? 'bg-secondary' : '' }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 {{ $subscription->endDate->isPast() ? 'text-white' : '' }}">Check-in Calendar</h5>
                        @if ($subscription->endDate->isPast())
                            <small class="text-white float-end">This Member's Subscription has Expired</small>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="checkin-calendar">
                            @foreach ($days as $day)
                                @php
                                    $hasCheckin = isset($checkinData[$day->format('Y-m-d')]);
                                    $checkinCount = $hasCheckin ? $checkinData[$day->format('Y-m-d')]['count'] : 0;
                                    $bgClass = $hasCheckin ? 'bg-primary' : 'bg-secondary';
                                    if ($subscription->endDate->isPast()) {
                                        $bgClass = $hasCheckin ? 'bg-info' : 'bg-danger';
                                    }

                                    // Format the tooltip content
                                    $tooltipDate = $day->format('M d, Y');
                                    $tooltipContent = $hasCheckin
                                        ? "<div class='tooltip-header'>{$tooltipDate} ({$checkinCount} check-ins)</div>"
                                        : "<div class='tooltip-header'>{$tooltipDate} (No check-ins)</div>";

                                    if ($hasCheckin && isset($checkinData[$day->format('Y-m-d')]['times'])) {
                                        $times = is_array($checkinData[$day->format('Y-m-d')]['times'])
                                            ? $checkinData[$day->format('Y-m-d')]['times']
                                            : [$checkinData[$day->format('Y-m-d')]['times']];

                                        $tooltipContent .= "<div class='tooltip-times'>";
                                        foreach ($times as $time) {
                                            $tooltipContent .= "<div class='tooltip-time'>• {$time}</div>";
                                        }
                                        $tooltipContent .= '</div>';
                                    }
                                @endphp

                                <div class="calendar-day {{ $bgClass }}" data-bs-toggle="tooltip"
                                    data-bs-placement="top" data-bs-html="true" title="{{ $tooltipContent }}"></div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrCodeModalLabel">{{ $member->getName() }}'s QR Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="{{ $subscription->member->qrCode ? asset('storage/' . $subscription->member->qrCode->path) : '' }}"
                        alt="Member QR Code" class="img-fluid">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl, {
                        html: true
                    });
                });
            });
        </script>
    @endpush
@endsection
