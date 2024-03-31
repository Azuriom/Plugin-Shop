@extends('admin.layouts.admin')

@section('title', $package->name)

@section('content')
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card flex-fill w-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        {{ trans('shop::admin.statistics.recent') }}
                    </h5>
                </div>
                <div class="card-body pt-2 pb-3">
                    <div class="tab-content mb-3">
                        <div class="tab-pane fade show active" id="monthlyChart" role="tabpanel" aria-labelledby="monthlyChartTab">
                            <div class="chart">
                                <canvas id="paymentsPerMonthsChart"></canvas>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="dailyChart" role="tabpanel" aria-labelledby="dailyChartTab">
                            <div class="chart">
                                <canvas id="paymentsPerDaysChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-pills" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="monthlyChartTab" data-bs-toggle="pill" href="#monthlyChart" role="tab" aria-controls="monthlyChart" aria-selected="true">
                                {{ trans('messages.range.months') }}
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="dailyChartTab" data-bs-toggle="pill" href="#dailyChart" role="tab" aria-controls="dailyChart" aria-selected="false">
                                {{ trans('messages.range.days') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h5 class="card-title mb-0">
                        {{ trans('shop::admin.gateways.title') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-pie py-4">
                        <canvas id="gatewaysChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('footer-scripts')
    <script src="{{ asset('vendor/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('admin/js/charts.js') }}"></script>
    <script>
        createPieChart('gatewaysChart', @json($gatewaysChart));
        createLineChart('paymentsPerMonthsChart', @json($paymentsPerMonths), '{{ trans('shop::admin.statistics.total') }}')
        createLineChart('paymentsPerDaysChart', @json($paymentsPerDays), '{{ trans('shop::admin.statistics.total') }}')
    </script>
@endpush
