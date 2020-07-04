@extends('admin.layouts.admin')

@section('title', trans('shop::admin.statistics.title'))

@section('content')

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div
                                class="text-xs font-weight-bold text-primary text-uppercase mb-1">{{ trans('shop::admin.statistics.stats.global') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{numberFormat($payment)}}</div>
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
                            <div
                                class="text-xs font-weight-bold text-primary text-uppercase mb-1">{{ trans('shop::admin.statistics.stats.estimated') }}</div>
                            <div
                                class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($estimated, 0, '.', ' ') }} {{currency_display(currency())}}</div>
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
        <div class="col-xl-12 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ trans('shop::admin.statistics.stats.month') }}</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="newPaymentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('footer-scripts')
    <script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>
    <script>
        Chart.defaults.global.defaultFontColor = '#858796';

        const paymentsKey = @json($payments->keys());
        const paymentsValue = @json($payments->values());

        new Chart(document.getElementById('newPaymentChart'), {
            type: 'line',
            data: {
                labels: paymentsKey,
                datasets: [{
                    label: 'Payments',
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: paymentsValue,
                }],
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{
                        /*time: {
                            unit: 'date'
                        },*/
                        gridLines: {
                            display: false,
                            drawBorder: false
                        },
                        /*ticks: {
                            maxTicksLimit: 7
                        }*/
                    }],
                    yAxes: [{
                        /*ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                        },*/
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }],
                },
                legend: {
                    display: false
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
                }
            }
        });
    </script>
@endpush
