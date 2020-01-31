@extends('layouts.app')

@section('title', trans('shop::messages.payment.title'))

@push('footer-scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripe = Stripe('{{ $stripeApiKey }}');
        stripe.redirectToCheckout({
            sessionId: '{{ $checkoutSessionId }}'
        }).then(function (result) {
            //
        });
    </script>
@endpush

@section('content')
    <div class="container content">
        <h1>{{ trans('shop::messages.payment.title') }}</h1>

        <p>{{ trans('shop::messages.payment.redirect-info') }}</p>
    </div>
@endsection
