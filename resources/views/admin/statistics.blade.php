@extends('admin.layouts.admin')

@section('title', trans('shop::admin.statistics.title'))

@section('content')
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ trans('shop::admin.statistics.month') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $monthPaymentsCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ trans('shop::admin.statistics.month-estimated') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $monthPaymentsTotal }} {{ currency_display() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ trans('shop::admin.statistics.count') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $paymentsCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ trans('shop::admin.statistics.estimated') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $paymentsTotal }} {{ currency_display() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Area Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ trans('shop::admin.statistics.recent-payments') }}</h6>
                </div>
                <div class="card-body">
                    <div class="tab-content mb-3">
                        <div class="tab-pane fade show active" id="monthlyChart" role="tabpanel" aria-labelledby="monthlyChartTab">
                            <div class="chart-area">
                                <canvas id="paymentsPerMonthsChart"></canvas>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="dailyChart" role="tabpanel" aria-labelledby="dailyChartTab">
                            <div class="chart-area">
                                <canvas id="paymentsPerDaysChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-pills" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="monthlyChartTab" data-toggle="pill" href="#monthlyChart" role="tab" aria-controls="monthlyChart" aria-selected="true">
                                {{ trans('messages.range.months') }}
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="dailyChartTab" data-toggle="pill" href="#dailyChart" role="tab" aria-controls="dailyChart" aria-selected="false">
                                {{ trans('messages.range.days') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Pie Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ trans('shop::admin.gateways.title') }}</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="gatewaysChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ trans('shop::admin.packages.title') }}</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
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
