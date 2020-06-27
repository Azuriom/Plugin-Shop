@extends('layouts.app')

@section('title', trans('shop::messages.payment.title'))

@push('footer-scripts')
    <script>
        document.querySelectorAll('.payment-method').forEach(function (el) {
            el.addEventListener('click', function (ev) {
                ev.preventDefault();

                const form = document.getElementById('submitForm');
                form.action = el.href;
                form.submit();
            });
        });
    </script>
@endpush

@section('content')
    <div class="container content">
        <h1>{{ trans('shop::messages.payment.title') }}</h1>

        <div class="row">
            @forelse($gateways as $gateway)
                <div class="col-md-3">
                    <div class="card shadow-sm mb-3">
                        <a href="{{ route('shop.payments.pay', $gateway->type) }}" class="payment-method">
                            <div class="card-body text-center">
                                <img src="{{ $gateway->paymentMethod()->image() }}" style="max-height: 45px" class="img-fluid" alt="{{ $gateway->name }}">
                            </div>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col">
                    <div class="alert alert-warning" role="alert">
                        {{ trans('shop::messages.payment.empty') }}
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <form method="POST" id="submitForm">
        @csrf
    </form>
@endsection
