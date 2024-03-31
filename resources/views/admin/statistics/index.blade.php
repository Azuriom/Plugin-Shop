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
                                <i class="bi bi-cash"></i>
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
                                {{ trans('shop::admin.statistics.month_estimated') }}
                            </h5>
                        </div>

                        <div class="col-auto">
                            <div class="stat text-primary">
                                <i class="bi bi-coin"></i>
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
                                <i class="bi bi-cash-coin"></i>
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
                                <i class="bi bi-cash-stack"></i>
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

        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h5 class="card-title mb-0">
                        {{ trans('shop::admin.packages.title') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-dark">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">{{ trans('shop::messages.fields.price') }}</th>
                                <th scope="col">{{ trans('shop::admin.statistics.count') }}</th>
                                <th scope="col">{{ trans('shop::admin.statistics.total') }}</th>
                                <th scope="col">{{ trans('messages.fields.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($packages as $package)
                                <tr>
                                    <th scope="row">{{ $package->id }}</th>
                                    <td>{{ $package->name  }}</td>
                                    <td>{{ $package->count }}</td>
                                    <td>{{ shop_format_amount($package->total) }}</td>
                                    <td>
                                        <a href="{{ route('shop.admin.statistics.package', $package) }}" class="mx-1" title="{{ trans('messages.actions.show') }}" data-bs-toggle="tooltip"><i class="bi bi-graph-up"></i></a>
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
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
        createPieChart('itemsChart', @json($packages->pluck('count', 'name')));

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

        function createShopMultiLineChart(elementId, data, labelNames) {
            const colors = ['#3b7ddd', '#1cbb8c', '#17a2b8', '#fcb92c'];
            let count = 0;

            if (!Array.isArray(labelNames) && data.length > 0) {
                labelNames = Object.keys(data[0].data)
            }

            new Chart(document.getElementById(elementId), {
                type: 'line',
                data: {
                    labels: labelNames.reverse(),
                    datasets: data.map(function (value) {
                        const color = colors[count++ % colors.length];

                        return {
                            label: value.label,
                            backgroundColor: 'rgba(78, 115, 223, 0.05)',
                            borderColor: color,
                            pointRadius: 3,
                            pointBackgroundColor: color,
                            pointBorderColor: color,
                            pointHoverRadius: 3,
                            pointHoverBackgroundColor: color,
                            pointHoverBorderColor: color,
                            pointHitRadius: 10,
                            pointBorderWidth: 2,
                            tension: 0.25,
                            data: Object.values(value.data).reverse(),
                        }
                    }),
                },
                options: {
                    maintainAspectRatio: false,
                    hover: {
                        intersect: true,
                    },
                    plugins: {
                        filler: {
                            propagate: false,
                        },
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            intersect: false,
                        },
                    },
                    scales: {
                        x: {
                            reverse: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0)'
                            },
                        },
                        y: {
                            ticks: {
                                stepSize: 1000
                            },
                            display: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0)',
                            },
                        }
                    }
                }
            });
        }
    </script>
@endpush
