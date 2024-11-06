@extends('layouts/AdminblankLayout')

@section('title', 'Membership Plans - Revenue')

@section('style-sheet')
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/v/dt/dt-2.1.8/fh-4.0.1/r-3.0.3/sc-2.4.3/sb-1.8.1/sp-2.3.3/datatables.min.css"
        rel="stylesheet">
@endsection

@section('content')
    <div class="row">
        <div class="col-xl">
            <div class="card mb-6">
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
                                                <td>{{ $revenue[$plan->name] }}</td>
                                                <td>{{ $revenue[$plan->name] * (int) $plan->price }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!--/ DataTable with Buttons -->
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
        document.getElementById('toggleTable').addEventListener('click', function() {
            const tableContainer = document.getElementById('tableContainer');
            tableContainer.style.display = tableContainer.style.display === 'none' ? 'block' : 'none';
        });
    </script>
@endsection
