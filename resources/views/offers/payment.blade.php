@extends('layouts.app')

@section('title', trans('shop::messages.offers.gateway'))

@section('content')
    <h1>{{ trans('shop::messages.offers.gateway') }}</h1>

    <div class="row gy-3">
        @forelse($gateways as $gateway)
            <div class="col-md-3">
                <div class="card">
                    <a href="{{ route('shop.offers.buy', $gateway) }}" class="payment-method">
                        <div class="card-body text-center">
                            <img src="{{ $gateway->paymentMethod()->image() }}" style="max-height: 45px" class="img-fluid" alt="{{ $gateway->name }}">
                        </div>
                    </a>
                </div>
            </div>
        @empty
            <div class="col">
                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-exclamation-circle"></i> {{ trans('shop::messages.payment.empty') }}
                </div>
            </div>
        @endforelse
    </div>
@endsection
