@extends('layouts/ReceptionistblankLayout')

@section('title', 'Members - List Member')

@section('style-sheet')
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/v/dt/dt-2.1.8/fh-4.0.1/r-3.0.3/sc-2.4.3/sb-1.8.1/sp-2.3.3/datatables.min.css"
        rel="stylesheet">
    <!-- Add Select2 CSS if not already included in your layout -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
    <div class="row">
        <div class="col-xl">
            <div class="card mb-6">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Members List</h5>
                </div>
                <div class="container-xxl flex-grow-1 container-p-y">
                    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                        <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="toast-header bg-success text-white">
                                <strong class="me-auto">Success</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="toast"
                                    aria-label="Close"></button>
                            </div>
                            <div class="toast-body">
                                Member updated successfully!
                            </div>
                        </div>
                    </div>
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
                    </div>

                    <div class="table-responsive text-nowrap">
                        <!-- DataTable with Buttons -->
                        <div class="card">
                            <div class="card-datatable table-responsive">
                                <table class="datatables-members table border-top" id="membersTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Membership Plan</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($members as $member)
                                            <tr>
                                                <td>{{ $member->id }}</td>
                                                <td>{{ $member->getName() }}</td>
                                                <td>{{ $member->email }}</td>
                                                <td>{{ $member->active_subscription->membership_plan->name ?? ($member->pending_subscription?->membership_plan->name ?? 'null') }}
                                                </td>
                                                <td>
                                                    <div class="d-inline-block">
                                                        <a href="{{ route('members.show', $member->id) }}"
                                                            class="btn rounded-pill btn-outline-info btn-sm">
                                                            <i class="tf-icons ri-information-line me-1"></i> Info
                                                        </a>
                                                        <button type="button"
                                                            class="btn rounded-pill btn-outline-primary btn-sm edit-user-btn"
                                                            data-user-id="{{ $member->id }}">
                                                            <i class="tf-icons ri-edit-line me-1"></i> Edit
                                                        </button>
                                                    </div>
                                                </td>
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

    <!-- Add this at the end of your content section -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditUser" aria-labelledby="offcanvasEditUserLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasEditUserLabel" class="offcanvas-title">Edit User</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 h-100">
            <form class="edit-user pt-0 fv-plugins-bootstrap5 fv-plugins-framework" id="editUserForm"
                novalidate="novalidate">
                @csrf
                <input type="hidden" name="id" id="user_id">

                <div class="mb-3">
                    <label for="firstname" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="firstname" name="firstname" required>
                </div>

                <div class="mb-3">
                    <label for="lastname" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="lastname" name="lastname" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone_number" name="phone_number" required>
                </div>

                <div class="mb-3">
                    <label for="sex" class="form-label">Sex</label>
                    <select class="form-select" id="sex" name="sex" required>
                        <option value="">Select</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="current_weight" class="form-label">Current Weight</label>
                    <input type="number" class="form-control" id="current_weight" name="current_weight"
                        step="0.01">
                </div>

                <div class="mb-3">
                    <label for="target_weight" class="form-label">Target Weight</label>
                    <input type="number" class="form-control" id="target_weight" name="target_weight" step="0.01">
                </div>

                <div class="mb-3">
                    <label for="target_weight" class="form-label">Height</label>
                    <input type="number" class="form-control" id="length" name="length" step="0.01">
                </div>

                <div class="mb-3">
                    <label for="goal" class="form-label">Goal</label>
                    <textarea class="form-control" id="goal" name="goal" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Update Member</button>
            </form>
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
            // Initialize DataTable
            var table = new DataTable('#membersTable');

            // Edit user button click handler
            $('#membersTable').on('click', '.edit-user-btn', function() {
                var userId = $(this).data('user-id');
                // Fetch user data and populate the form
                fetchUserData(userId);
                // Open the offcanvas
                var offcanvasEditUser = new bootstrap.Offcanvas(document.getElementById(
                    'offcanvasEditUser'));
                offcanvasEditUser.show();
            });

            // Form submission handler
            $('#editUserForm').on('submit', function(e) {
                e.preventDefault();
                // Handle form submission via AJAX
                updateUser();
            });
        });

        function fetchUserData(userId) {
            $.ajax({
                url: '/api/members/' + userId,
                method: 'GET',
                success: function(data) {
                    $('#user_id').val(data.id);
                    $('#firstname').val(data.firstname);
                    $('#lastname').val(data.lastname);
                    $('#email').val(data.email);
                    $('#phone_number').val(data.phone_number);
                    $('#sex').val(data.sex);
                    $('#length').val(data.length);
                    $('#current_weight').val(data.current_weight);
                    $('#target_weight').val(data.target_weight);
                    $('#goal').val(data.goal);
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching member data:", error);
                }
            });
        }

        function updateUser() {
            var formData = $('#editUserForm').serialize();
            $.ajax({
                url: '/api/members/' + $('#user_id').val(),
                method: 'PUT',
                data: formData,
                success: function(response) {
                    console.log("Member updated successfully:", response);
                    var offcanvasEditUser = bootstrap.Offcanvas.getInstance(document.getElementById(
                        'offcanvasEditUser'));
                    offcanvasEditUser.hide();
                    var successToast = new bootstrap.Toast(document.getElementById('successToast'));
                    successToast.show();

                    // Reload the page after toast is shown
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500); // 1.5 second delay to allow toast to be visible
                },
                error: function(xhr, status, error) {
                    console.error("Error updating user:", error);
                    // Handle error (e.g., show error messages to the user)
                }
            });
        }
    </script>
@endsection
