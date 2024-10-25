@extends('layouts/AdminblankLayout')

@section('title', 'Members - Add Member')

@section('content')
    <div class="row">
        <div class="col-xl">
            <div class="card mb-6">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Add New Member</h5>
                    <small class="text-danger float-end">Fields marked with * are required</small>
                </div>
                <div class="card-body">
                    <form action="{{ route('Members-store') }}" method="POST">
                        @csrf
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
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group input-group-merge mb-3">
                                    <span class="input-group-text"><i class="ri-user-line ri-20px"></i></span>
                                    <input type="text" name="firstname" class="form-control" placeholder="First Name *"
                                        required />
                                </div>
                                <div class="input-group input-group-merge mb-3">
                                    <span class="input-group-text"><i class="ri-mail-line ri-20px"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="Email *"
                                        required />
                                </div>
                                <div class="input-group input-group-merge mb-3">
                                    <span class="input-group-text"><i class="ri-user-line ri-20px"></i></span>
                                    <select name="sex" class="form-select" required>
                                        <option value="">Select Gender *</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
                                <div class="input-group input-group-merge mb-3">
                                    <span class="input-group-text"><i class="ri-scales-3-line ri-20px"></i></span>
                                    <input type="number" name="current_weight" class="form-control"
                                        placeholder="Current Weight (kg)" />
                                </div>
                                <div class="input-group input-group-merge mb-3">
                                    <span class="input-group-text"><i class="ri-calendar-line ri-20px"></i></span>
                                    <input type="date" name="startDate" class="form-control" placeholder="Start Date" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group input-group-merge mb-3">
                                    <span class="input-group-text"><i class="ri-user-line ri-20px"></i></span>
                                    <input type="text" name="lastname" class="form-control" placeholder="Last Name *"
                                        required />
                                </div>
                                <div class="input-group input-group-merge mb-3">
                                    <span class="input-group-text"><i class="ri-phone-fill ri-20px"></i></span>
                                    <input type="tel" name="phone_number" class="form-control phone-mask"
                                        placeholder="Phone Number *" required />
                                </div>
                                <div class="input-group input-group-merge mb-3">
                                    <span class="input-group-text"><i class="ri-flag-line ri-20px"></i></span>
                                    <input type="text" name="goal" class="form-control" placeholder="Fitness Goal"
                                        required />
                                </div>
                                <div class="input-group input-group-merge mb-3">
                                    <span class="input-group-text"><i class="ri-scales-3-line ri-20px"></i></span>
                                    <input type="number" name="target_weight" class="form-control"
                                        placeholder="Target Weight (kg)" />
                                </div>
                                <div class="input-group input-group-merge mb-3">
                                    <span class="input-group-text"><i class="ri-user-line ri-20px"></i></span>
                                    <select name="membership_plan" class="form-select" required>
                                        <option value="">MembershipPlan *</option>
                                        @foreach ($memberships as $membership)
                                            <option value="{{ $membership->id }}">{{ $membership->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Add Member</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
