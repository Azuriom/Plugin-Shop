@extends('layouts.app')

@section('title', 'PayPal Checkout')

@push('styles')
    <style>
        apple-pay-button {
            --apple-pay-button-width: 100%;
            --apple-pay-button-height: 44px;
        }
    </style>
@endpush

@push('scripts')
    @isset($subscriptionPlanId)
        <script src="https://www.paypal.com/sdk/js?client-id={{ $clientId }}&vault=true&intent=subscription&currency={{ $currency }}&components=buttons"></script>
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
        <script src="https://applepay.cdn-apple.com/jsapi/v1/apple-pay-sdk.js"></script>
        <script src="https://www.paypal.com/sdk/js?client-id={{ $clientId }}&currency={{ $currency }}&components=buttons,applepay"></script>
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

            //
            // Apple Pay
            //
            async function setupApplePay() {
                const applePay = paypal.Applepay();
                const applePayConfig = await applePay.config();

                if (!applePayConfig.isEligible) {
                    console.log('This PayPay account is not eligible for Apple Pay.');
                    return;
                }

                document.getElementById("applepay-container").innerHTML = '<apple-pay-button id="apple-pay" buttonstyle="black" type="buy"">';

                document.getElementById('apple-pay').addEventListener('click', async function() {
                    let active = true;

                    const paymentRequest = {
                        countryCode: applePayConfig.countryCode,
                        currencyCode: applePayConfig.currencyCode,
                        merchantCapabilities: applePayConfig.merchantCapabilities,
                        supportedNetworks: applePayConfig.supportedNetworks,
                        requiredBillingContactFields: ['postalAddress'],
                        total: {
                            amount: {{ $payment->price }},
                            label: '{{ $description }}',
                            type: 'final',
                        },
                    };

                    const session = new ApplePaySession(4, paymentRequest);

                    session.onvalidatemerchant = (event) => {
                        applePay.validateMerchant({
                                validationUrl: event.validationURL,
                            })
                            .then((payload) => {
                                if (active) {
                                    session.completeMerchantValidation(payload.merchantSession);
                                }
                            })
                            .catch((err) => {
                                console.error(err);
                                showPayPalError(err.toString());
                                session.abort();
                            });
                    };

                    session.onpaymentmethodselected = () => {
                        session.completePaymentMethodSelection({
                            newTotal: paymentRequest.total,
                        });
                    };

                    session.onpaymentauthorized = async (event) => {
                        try {
                            await applePay.confirmOrder({
                                orderId: '{{ $paypalId }}',
                                token: event.payment.token,
                                billingContact: event.payment.billingContact,
                            });

                            await axios.post('{{ $captureUrl }}/{{ $paypalId }}');

                            session.completePayment({
                                status: window.ApplePaySession.STATUS_SUCCESS,
                            });

                            window.location.href = '{{ $successUrl }}';
                        } catch (err) {
                            console.error(err);
                            showPayPalError(err.toString());
                            session.completePayment({
                                status: window.ApplePaySession.STATUS_FAILURE,
                            });
                        }
                    };

                    session.oncancel = () => {
                        active = false;
                        console.log('Apple Pay session cancelled.');
                    };

                    session.begin();
                });
            }

            document.addEventListener('DOMContentLoaded', () => {
                if(window.ApplePaySession && ApplePaySession.canMakePayments()) {
                    setupApplePay()
                }
            });
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
                    <span class="badge bg-primary">{{ $payment->formatPrice() }}</span>
                </h2>

                <div id="applepay-container"></div>
                <div id="paypal-button-container"></div>
            </div>

            <a href="{{ route('shop.home') }}" class="btn btn-secondary">
                <i class="bi bi-x-lg"></i> {{ trans('messages.actions.cancel') }}
            </a>
        </div>
    </div>
@endsection
