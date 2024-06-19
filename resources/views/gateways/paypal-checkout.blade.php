@extends('layouts.app')

@section('title', 'PayPal Checkout')

@push('styles')
    <style>
        apple-pay-button {
            --apple-pay-button-width: 100%;
            --apple-pay-button-height: 44px;
        }

        .googlepay-button {
            height: 44px;
            margin-top: 0.5rem;;
        }

        #loader {
            display: flex;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(70, 70, 70, 0.6);
            z-index: 1000;
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
        <script src="https://pay.google.com/gp/p/js/pay.js"></script>
        <script src="https://applepay.cdn-apple.com/jsapi/v1/apple-pay-sdk.js"></script>
        <script src="https://www.paypal.com/sdk/js?client-id={{ $clientId }}&currency={{ $currency }}&components=buttons,applepay,googlepay"></script>
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
                    } catch (err) {
                        console.error(err);
                        showPayPalError(err.toString());
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
                    console.log('This PayPal account is not eligible for Apple Pay.');
                    return;
                }

                document.getElementById("applepay-container").innerHTML = '<apple-pay-button id="apple-pay" buttonstyle="black" type="buy"">';

                document.getElementById('apple-pay').addEventListener('click', async function () {
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
                        }).then((payload) => {
                            if (active) {
                                session.completeMerchantValidation(payload.merchantSession);
                            }
                        }).catch((err) => {
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

            //
            // Google Pay
            //
            let googlePaymentsClient = null;
            let googlePayConfig = null;

            async function setupGooglePay() {
                googlePayConfig = await paypal.Googlepay().config();

                if (!googlePayConfig.isEligible) {
                    console.log('This PayPal account is not eligible for Google Pay.');
                    return;
                }

                googlePaymentsClient = new google.payments.api.PaymentsClient({
                    environment: '{{ $sandbox ? 'TEST' : 'PRODUCTION'}}',
                    paymentDataCallbacks: {
                        onPaymentAuthorized: (paymentData) => new Promise(function (resolve) {
                            processPayment(paymentData)
                                .then(function () {
                                    resolve({ transactionState: 'SUCCESS' });
                                })
                                .catch(function () {
                                    resolve({ transactionState: 'ERROR' });
                                });
                        }),
                    }
                });

                googlePaymentsClient.isReadyToPay({
                    allowedPaymentMethods: googlePayConfig.allowedPaymentMethods,
                    apiVersion: googlePayConfig.apiVersion,
                    apiVersionMinor: googlePayConfig.apiVersionMinor,
                }).then(function (response) {
                    if (response.result) {
                        const button = googlePaymentsClient.createButton({
                            buttonSizeMode: 'fill',
                            onClick: async () => googlePaymentsClient.loadPaymentData({
                                apiVersion: googlePayConfig.apiVersion,
                                apiVersionMinor: googlePayConfig.apiVersionMinor,
                                allowedPaymentMethods: googlePayConfig.allowedPaymentMethods,
                                transactionInfo: {
                                    countryCode: googlePayConfig.countryCode,
                                    currencyCode: '{{ $currency }}',
                                    totalPriceStatus: 'FINAL',
                                    totalPrice: '{{ $payment->price }}',
                                    totalPriceLabel: '{{ $description }}',
                                    transactionId: '{{ $paypalId }}'
                                },
                                merchantInfo: googlePayConfig.merchantInfo,
                                callbackIntents: ['PAYMENT_AUTHORIZATION']
                            }),
                        });

                        const container = document.getElementById('googlepay-container');
                        container.appendChild(button);
                        container.classList.add('googlepay-button');
                    }
                }).catch(function (err) {
                    console.error(err);
                    showPayPalError(err.toString());
                });
            }

            async function processPayment(paymentData) {
                const orderId = '{{ $paypalId }}';
                const loader = document.getElementById('pay-loader');

                console.log('Payment Data:', paymentData);

                try {
                    const order = await paypal.Googlepay().confirmOrder({
                        orderId,
                        paymentMethodData: paymentData.paymentMethodData
                    });

                    if (order.status === 'PAYER_ACTION_REQUIRED'){
                        console.log('Payer Action Required for Google Pay')

                        paypal.Googlepay().initiatePayerAction({ orderId }).then(async () => {
                            const orderResponse = await fetch(`/api/orders/${id}`, {
                                method: "GET"
                            }).then(res => res.json())

                            console.log("3DS Contingency Result Fetched");
                            console.log(orderResponse?.payment_source?.google_pay?.card?.authentication_result)
                            loader.classList.remove('d-none');

                            await axios.post('{{ $captureUrl }}/' + orderId);

                            setTimeout(() => {
                                window.location.href = '{{ $successUrl }}';
                            }, 250);
                        })
                    } else {
                        await axios.post('{{ $captureUrl }}/' + orderId);

                        setTimeout(() => {
                            window.location.href = '{{ $successUrl }}';
                        }, 250);
                    }

                    return { transactionState: 'SUCCESS' }
                } catch (err) {
                    console.error(err);
                    showPayPalError(err.toString());

                    return {
                        transactionState: 'ERROR',
                        error: {
                            message: err.message
                        }
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                if (window.ApplePaySession && ApplePaySession.canMakePayments()) {
                    setupApplePay();
                }

                if (google && paypal.Googlepay) {
                    setupGooglePay();
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

                <div id="pay-buttons" class="position-relative mb-1">
                    <div id="pay-loader" class="d-none h-100 position-absolute align-items-center justify-content-center">
                        <div class="spinner-border text-white" role="status"></div>
                    </div>
                    <div id="applepay-container"></div>
                    <div id="googlepay-container"></div>
                    <div id="paypal-button-container" class="mt-3"></div>
                </div>
            </div>

            <a href="{{ route('shop.home') }}" class="btn btn-secondary">
                <i class="bi bi-x-lg"></i> {{ trans('messages.actions.cancel') }}
            </a>
        </div>
    </div>
@endsection
