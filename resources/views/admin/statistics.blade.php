@extends('admin.layouts.admin')

@section('title', trans('shop::admin.statistics.title'))

@section('content')
    <div class="row">
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title mb-0">
                                {{ trans('shop::admin.statistics.month') }}
                            </h5>
                        </div>

                        <div class="col-auto">
                            <div class="stat text-primary">
                                <i class="fas fa-money-bill"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3">{{ $monthPaymentsCount }}</h1>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title mb-0">
                                {{ trans('shop::admin.statistics.month-estimated') }}
                            </h5>
                        </div>

                        <div class="col-auto">
                            <div class="stat text-primary">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3">
                        {{ $monthPaymentsTotal }} {{ currency_display() }}
                    </h1>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title mb-0">
                                {{ trans('shop::admin.statistics.count') }}
                            </h5>
                        </div>

                        <div class="col-auto">
                            <div class="stat text-primary">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3">{{ $paymentsCount }}</h1>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title mb-0">
                                {{ trans('shop::admin.statistics.estimated') }}
                            </h5>
                        </div>

                        <div class="col-auto">
                            <div class="stat text-primary">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3">
                        {{ $paymentsTotal }} {{ currency_display() }}
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card flex-fill w-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        {{ trans('shop::admin.statistics.recent-payments') }}
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

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h5 class="card-title mb-0">
                        {{ trans('shop::admin.packages.title') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-pie py-4">
                        <canvas id="itemsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('footer-scripts')
    <script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('admin/js/charts.js') }}"></script>
    <script>
        createPieChart('gatewaysChart', @json($gatewaysChart));
        createPieChart('itemsChart', @json($itemsChart));

        createShopMultiLineChart('paymentsPerMonthsChart', [
            {
                label: '{{ trans('shop::admin.statistics.total') }}',
                data: @json($paymentsPerMonths)
            },
                @foreach ($gatewaysPayments as $gatewayPayments)
            {
                label: '{{ $gatewayPayments['name'] }}',
                data: @json($gatewayPayments['totalByMonths']),
            },
            @endforeach
        ]);
        createShopMultiLineChart('paymentsPerDaysChart', [
            {
                label: '{{ trans('shop::admin.statistics.total') }}',
                data: @json($paymentsPerDays)
            },
                @foreach ($gatewaysPayments as $gatewayPayments)
            {
                label: '{{ $gatewayPayments['name'] }}',
                data: @json($gatewayPayments['totalByDays']),
            },
            @endforeach
        ]);

        function createShopMultiLineChart(elementId, values, labels) {
            const colors = ['#4e73df', '#1cc88a', '#36b9cc', '#e9aa0b'];
            let count = 0;

            if (!Array.isArray(labels) && values.length > 0) {
                labels = Object.keys(values[0].data)
            }

            const datasets = values.map(function (value) {
                const color = colors[count++ % colors.length];

                return {
                    label: value.label,
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: color,
                    pointRadius: 3,
                    pointBackgroundColor: color,
                    pointBorderColor: color,
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: color,
                    pointHoverBorderColor: color,
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: Object.values(value.data),
                }
            });

            new Chart(document.getElementById(elementId), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: datasets,
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        xAxes: [{
                            time: {
                                unit: 'date'
                            },
                            gridLines: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                maxTicksLimit: 7
                            },
                        }],
                        yAxes: [{
                            ticks: {
                                maxTicksLimit: 5,
                                padding: 10,
                            },
                            gridLines: {
                                color: "rgb(234, 236, 244)",
                                zeroLineColor: "rgb(234, 236, 244)",
                                drawBorder: false,
                                borderDash: [2],
                                zeroLineBorderDash: [2],
                            },
                        }],
                    },
                    legend: {
                        display: true
                    },
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        titleMarginBottom: 10,
                        titleFontColor: '#6e707e',
                        titleFontSize: 14,
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        intersect: false,
                        mode: 'index',
                        caretPadding: 10,
                    },
                }
            });
        }
    </script>
@endpush
