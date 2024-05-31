@extends('layouts.app')

@section('title', 'PayPal Checkout')

@push('scripts')
    @isset($subscriptionPlanId)
        <script src="https://www.paypal.com/sdk/js?client-id={{ $clientId }}&vault=true&intent=subscription"></script>
        <script>
            window.paypal.Buttons({
                createSubscription: function(data, actions) {
                    return actions.subscription.create({
                        'plan_id': '{{ $subscriptionPlanId }}',
                    });
                },
                onApprove: function(data, actions) {
                    actions.redirect('{{ $successUrl }}');
                }
            }).render('#paypal-button-container');
        </script>
    @else
        <script src="https://www.paypal.com/sdk/js?client-id={{ $clientId }}"></script>
        <script>
            window.paypal.Buttons({
                createOrder() {
                    return '{{ $paypalId }}';
                },
                async onApprove(data, actions) {
                    try {
                        const response = await axios.post('{{ $captureUrl }}/' + data.orderID);

                        // Three cases to handle:
                        //   (1) Recoverable INSTRUMENT_DECLINED -> call actions.restart()
                        //   (2) Other non-recoverable errors -> Show a failure message
                        //   (3) Successful transaction -> Show confirmation

                        const errorDetail = response.data?.details?.[0];

                        if (errorDetail?.issue === "INSTRUMENT_DECLINED") { // (1) Recoverable INSTRUMENT_DECLINED
                            return actions.restart();
                        }

                        if (errorDetail) { // (2) Other non-recoverable errors
                            throw new Error(`${errorDetail.description} (${orderData.debug_id})`);
                        }

                        actions.redirect('{{ $successUrl }}'); // (3) Successful transaction
                    } catch (error) {
                        console.error(error);
                        showPayPalError(error.toString());
                    }
                },
            }).render('#paypal-button-container');

            function showPayPalError(error) {
                const alertBox = document.getElementById('errorAlert');
                const errorMessage = document.getElementById('errorMessage');
                errorMessage.innerHTML = error;
                alertBox.classList.remove('d-none');
            }
        </script>
    @endisset
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="alert alert-danger d-none" id="errorAlert" role="status">
                <p>{{ trans('shop::messages.payment.error') }}</p>
                <span id="errorMessage"></span>
            </div>

            <div class="text-center">
                <h1>PayPal Checkout</h1>

                <h2 class="mb-5">
                    <span class="badge bg-primary">{{ $total }}</span>
                </h2>

                <div id="paypal-button-container"></div>
            </div>

            <a href="{{ route('shop.home') }}" class="btn btn-secondary">
                <i class="bi bi-x-lg"></i> {{ trans('messages.actions.cancel') }}
            </a>
        </div>
    </div>
@endsection
