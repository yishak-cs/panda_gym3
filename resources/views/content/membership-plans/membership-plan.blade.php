@extends('layouts/AdminblankLayout')

@section('title', 'Membership')

@section('content')
    <div class="col-12">
        <div class="card mb-6">
            <div class="card-body">
                <button class="btn btn-primary me-1" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample"
                    aria-expanded="false" aria-controls="collapseExample">
                    Add New Membership Plan
                </button>

                <div class="collapse" id="collapseExample">
                    <div class="d-grid d-sm-flex p-4 border">
                        <form class="p-6" action="{{ route('Membership_plans.store') }}" method="POST">
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
                                    <div class="form-floating form-floating-outline mb-6">
                                        <input type="text" name="name" class="form-control"
                                            id="exampleDropdownFormName" placeholder="Enter Plan Name" required>
                                        <label for="exampleDropdownFormName">Name</label>
                                    </div>
                                    <div class="form-floating form-floating-outline mb-6">
                                        <input type="number" name="duration" class="form-control"
                                            id="exampleDropdownFormDuration" placeholder="In Days" required />
                                        <label for="exampleDropdownFormDuration">Duration in Days</label>
                                    </div>
                                    <div class="form-floating form-floating-outline mb-6">
                                        <input type="number" name="price" class="form-control"
                                            id="exampleDropdownFormPrice" placeholder="Price in ETB" required />
                                        <label for="exampleDropdownFormPrice">Price</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating form-floating-outline mb-6">
                                        <input type="number" name="allowed_entries" class="form-control"
                                            id="exampleDropdownAllowed_entries" placeholder="Allowed entries count" />
                                        <label for="exampleDropdownAllowed_entries">Allowed Entries</label>
                                    </div>
                                    <div class="form-floating form-floating-outline mb-6">
                                        <textarea name="description" class="form-control h-px-100" id="exampleFormControlDescription"
                                            placeholder="Write Descriptions Here...." readonly></textarea>
                                        <label for="exampleFormControlDescription">Description</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Plan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-12 g-6">
        @foreach ($plans as $plan)
            <div class="col-md-4">
                <div class="card mb-6">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ $plan->name }}</h5>
                        <form action="{{ route('membership_plans.destroy', $plan->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn rounded-pill btn-outline-danger btn-sm">
                                <i class="tf-icons ri-delete-bin-line"></i>
                            </button>

                        </form>
                    </div>
                    <div class="card-body">
                        <div class="card-subtitle mb-3">{{ $plan->price }} ETB</div>
                        <p class="card-text">
                            {{ $plan->description }}
                        </p>
                        <div class="btn-group">
                            <button type="button" class="btn rounded-pill btn-outline-danger  btn-sm"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Edit
                            </button>
                            <div class="dropdown-menu w-px-300">
                                <form class="p-6" onsubmit="return false">
                                    <div class="form-floating form-floating-outline mb-6">
                                        <input type="email" class="form-control" id="exampleDropdownFormEmail1"
                                            placeholder="email@example.com">
                                        <label for="exampleDropdownFormEmail1">Email address</label>
                                    </div>
                                    <div class="form-floating form-floating-outline mb-6">
                                        <input type="password" class="form-control" id="exampleDropdownFormPassword1"
                                            placeholder="Password">
                                        <label for="exampleDropdownFormPassword1">Password</label>
                                    </div>
                                    <button type="button" class="btn btn-primary">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
