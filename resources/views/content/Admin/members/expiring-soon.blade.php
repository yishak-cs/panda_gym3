@extends('layouts/AdminblankLayout')

@section('title', 'Expiring Subscriptions')

@section('style-sheet')
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/v/dt/dt-2.1.8/fh-4.0.1/r-3.0.3/sc-2.4.3/sb-1.8.1/sp-2.3.3/datatables.min.css"
        rel="stylesheet">
@endsection

@section('content')
    <div class="row">
        <div class="col-xl">
            <div class="card mb-6">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Expiring Subscriptions</h5>
                </div>
                <div class="container-xxl flex-grow-1 container-p-y">
                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Filter Form -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="GET" action="{{ route('Members-expiring') }}" class="row g-3"
                                    id="filterForm">
                                    <div class="col-12">
                                        <label for="date" class="form-label">Expiration Date</label>
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="date" class="form-control" id="date" name="date"
                                                value="{{ request('date', now()->format('Y-m-d')) }}" required
                                                style="max-width: 250px;">
                                            <a href="{{ route('Members-expiring') }}" class="btn btn-outline-secondary">
                                                <i class="tf-icons ri-refresh-line me-1"></i> Reset
                                            </a>
                                        </div>
                                        <small class="text-muted">
                                            Select a date to find members whose subscriptions expire or expired on that date
                                        </small>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Results -->
                        <div class="table-responsive text-nowrap">
                            <div class="card">
                                <div class="card-datatable table-responsive">
                                    @if ($expiringMembers->count() > 0)
                                        <table class="datatables-members table border-top" id="expiringTable">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Phone</th>
                                                    <th>Membership Plan</th>
                                                    <th>Expiration Date</th>
                                                    <th>Status</th>
                                                    <th>Reason</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($expiringMembers as $item)
                                                    @php
                                                        $member = $item['member'];
                                                        $subscription = $item['subscription'];
                                                        $expirationDate = $item['expiration_date'];
                                                        $isExpired =
                                                            $item['expired_by_date'] || $item['expired_by_entries'];
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $member->id }}</td>
                                                        <td>{{ $member->getName() }}</td>
                                                        <td>{{ $member->email }}</td>
                                                        <td>{{ $member->phone_number }}</td>
                                                        <td>
                                                            <span class="badge rounded-pill bg-info">
                                                                {{ $subscription->membership_plan->name }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="fw-semibold">
                                                                {{ $expirationDate->format('Y-m-d') }}
                                                            </span>
                                                            <br>
                                                            <small class="text-muted">
                                                                @php
                                                                    $now = \Carbon\Carbon::now()->startOfDay();
                                                                    $expDate = $expirationDate->copy()->startOfDay();
                                                                    $diffDays = $expDate->diffInDays($now, false);
                                                                    $diffHours = $expDate->diffInHours($now, false);
                                                                @endphp
                                                                @if ($diffDays > 0)
                                                                    @if ($diffDays == 1)
                                                                        Yesterday
                                                                    @else
                                                                        {{ $diffDays }} days ago
                                                                    @endif
                                                                @elseif($diffDays < 0)
                                                                    @if (abs($diffDays) == 1)
                                                                        Tomorrow
                                                                    @else
                                                                        In {{ abs($diffDays) }} days
                                                                    @endif
                                                                @else
                                                                    @if (abs($diffHours) < 24)
                                                                        Today
                                                                    @else
                                                                        {{ abs($diffHours) }} hours
                                                                        {{ $diffDays > 0 ? 'ago' : 'from now' }}
                                                                    @endif
                                                                @endif
                                                            </small>
                                                        </td>
                                                        <td>
                                                            @if ($isExpired)
                                                                <span class="badge rounded-pill bg-danger">Expired</span>
                                                            @else
                                                                <span class="badge rounded-pill bg-warning">Expiring</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($item['expired_by_entries'])
                                                                <span class="badge rounded-pill bg-danger">
                                                                    <i class="tf-icons ri-close-circle-line me-1"></i>
                                                                    Entries Exceeded
                                                                </span>
                                                                <br>
                                                                <small class="text-muted">
                                                                    {{ $item['checkin_count'] }}/{{ $item['allowed_entries'] }}
                                                                    entries used
                                                                </small>
                                                                @if (isset($item['entries_exhaustion_date']) && $item['entries_exhaustion_date'])
                                                                    <br>
                                                                    <small class="text-info">
                                                                        <i class="tf-icons ri-calendar-line me-1"></i>
                                                                        Exhausted:
                                                                        {{ $item['entries_exhaustion_date']->format('Y-m-d') }}
                                                                    </small>
                                                                    @if ($item['entries_exhaustion_date']->format('Y-m-d') != $expirationDate->format('Y-m-d'))
                                                                        <br>
                                                                        <small class="text-warning">
                                                                            <i
                                                                                class="tf-icons ri-information-line me-1"></i>
                                                                            End date:
                                                                            {{ $subscription->endDate->format('Y-m-d') }}
                                                                        </small>
                                                                    @endif
                                                                @endif
                                                            @elseif($item['expired_by_date'])
                                                                <span class="badge rounded-pill bg-danger">
                                                                    <i class="tf-icons ri-calendar-close-line me-1"></i>
                                                                    Date Passed
                                                                </span>
                                                            @else
                                                                <span class="badge rounded-pill bg-warning">
                                                                    <i class="tf-icons ri-calendar-line me-1"></i>
                                                                    Date Expiring
                                                                </span>
                                                                @if (!is_null($item['allowed_entries']))
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        {{ $item['checkin_count'] }}/{{ $item['allowed_entries'] }}
                                                                        entries used
                                                                    </small>
                                                                @endif
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="d-inline-block">
                                                                <a href="{{ route('members.show', $member->id) }}"
                                                                    class="btn rounded-pill btn-outline-info btn-sm">
                                                                    <i class="tf-icons ri-information-line me-1"></i> View
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="tf-icons ri-inbox-line" style="font-size: 4rem; color: #ccc;"></i>
                                            <h5 class="mt-3">No Members Found</h5>
                                            <p class="text-muted">
                                                No members have subscriptions expiring or expired on
                                                {{ request('date', now()->format('Y-m-d')) }}.
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/v/dt/dt-2.1.8/fh-4.0.1/r-3.0.3/sc-2.4.3/sb-1.8.1/sp-2.3.3/datatables.min.js">
    </script>

    <script>
        $(document).ready(function() {
            @if ($expiringMembers->count() > 0)
                var table = new DataTable('#expiringTable', {
                    order: [
                        [5, 'asc']
                    ], // Sort by expiration date
                    pageLength: 25
                });
            @endif

            // Auto-submit form when date changes
            $('#date').on('change', function() {
                $('#filterForm').submit();
            });
        });
    </script>
@endsection
