@extends('layouts/AdminblankLayout')

@section('title', 'Dashboard - Analytics')

@section('vendor-style')
    @vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
    @vite('resources/assets/js/dashboards-analytics.js')
@endsection

@section('content')
    <div class="row gy-6">
        <!-- Congratulations card -->
        <div class="col-md-12 col-lg-4">
            <div class="card">
                <div class="card-body text-nowrap">
                    <h5 class="card-title mb-0 flex-wrap text-nowrap">Congratulations Norris! 🎉</h5>
                    <p class="mb-2">Best seller of the month</p>
                    <h4 class="text-primary mb-0">$42.8k</h4>
                    <p class="mb-2">78% of target 🚀</p>
                    <a href="javascript:;" class="btn btn-sm btn-primary">View Sales</a>
                </div>
                <img src="{{ asset('assets/img/illustrations/trophy.png') }}"
                    class="position-absolute bottom-0 end-0 me-5 mb-5" width="83" alt="view sales">
            </div>
        </div>
    </div>
@endsection
