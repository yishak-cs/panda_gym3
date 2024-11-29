@extends('layouts/ReceptionistblankLayout')

@section('title', 'Dashboard - Reception')
@section('page-style')
    <style>
        .content {
            max-width: 1200px;
            /* Set a maximum width for the content */
            margin: auto;
            /* Center the content */
            padding: 20px;
            /* Add some padding */
            background-color: #f8f9fa;
            /* Light background color */
            border-radius: 8px;
            /* Rounded corners */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            /* Subtle shadow */
        }

        .custom-indicators {
            position: relative;
            /* Ensure the indicators are positioned relative to the carousel */
            bottom: -21.875em;
            /* Move the indicators down */
            z-index: 10;
            /* Ensure they are above other elements */
            text-align: center;
            /* Center the indicators */
        }

        .custom-indicators button {
            background-color: rgba(255, 255, 255, 0.7);
            /* Semi-transparent white background */
            border: none;
            /* Remove default border */
            border-radius: 0%;
            /* Make them circular */
            width: 10px;
            /* Set width */
            height: 10px;
            /* Set height */
            margin: 0 5px;
            /* Add some spacing between buttons */
        }

        .custom-indicators .active {
            background-color: #ab2f2b;
            /* Change active button color */
        }

        .card:hover {
            transform: scale(1.05);
            transition: transform 0.2s;
        }

        /* New styles for carousel controls */
        .carousel-control-prev,
        .carousel-control-next {
            opacity: 0;
            /* Initially hidden */
            transition: opacity 0.3s ease;
            /* Smooth transition */
        }

        .carousel:hover .carousel-control-prev,
        .carousel:hover .carousel-control-next {
            opacity: 1;

            /* Change to a more visible color */
            border-radius: 50%;
            /* Keep them circular */
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: rgba(171, 47, 43, 0.8);
            /* Change icon color for better contrast */
            border-radius: 50%;
            /* Ensure the icon background is circular */
        }
    </style>
@endsection
@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toastEl = document.getElementById('errorToast');
            if (toastEl) {
                var toast = new bootstrap.Toast(toastEl, {
                    delay: 2000
                }); // 5 seconds delay
                toast.show();
            }
        });
    </script>
@endsection
@section('content')
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        @if (session('error'))
            <div id="errorToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-danger text-white">
                    <strong class="me-auto">Error</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    {{ session('error') }}
                </div>
            </div>
        @endif
    </div>
    <div class="content mt-6">
        <div class="col-md">
            <h5 class="mb-3 text-center text-primary">Membership Plans</h5>
            <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators custom-indicators">
                    @foreach ($plans as $index => $plan)
                        <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="{{ $index }}"
                            class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                            aria-label="Slide {{ $index + 1 }}"></button>
                    @endforeach
                </div>
                <div class="carousel-inner">
                    @foreach ($plans as $index => $plan)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="col-md-4">
                                    <div class="card mb-6 shadow-sm rounded">
                                        <div
                                            class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                            <h5 class="card-title mb-0">{{ $plan->name }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="card-subtitle mt-3 mb-3">{{ $plan->price }} ETB</div>
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item">Duration
                                                    <small class="text-primary float-end">{{ $plan->duration }} Days</small>
                                                </li>
                                                <li class="list-group-item">Allowed entries
                                                    <small
                                                        class="text-primary float-end">{{ $plan->allowed_entries == null ? 'Unlimited' : $plan->allowed_entries }}</small>
                                                </li>
                                                <li class="list-group-item">
                                                    Description
                                                    <ul class="footer">
                                                        <li><small class="text float">{{ $plan->description }}</small></li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <a class="carousel-control-prev" href="#carouselExample" role="button" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </a>
                <a class="carousel-control-next" href="#carouselExample" role="button" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </a>
            </div>
        </div>
    </div>
@endsection
