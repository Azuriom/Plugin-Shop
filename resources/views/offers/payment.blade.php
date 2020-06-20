@extends('layouts.app')

@section('title', trans('shop::messages.offers.title-payment'))

@section('content')
    <div class="container content">
        <h1>{{ trans('shop::messages.offers.title-payment') }}</h1>

        <div class="row">
            @foreach($gateways as $gateway)
                <div class="col-md-3">
                    <div class="card shadow-sm mb-3">
                        <a href="{{ route('shop.offers.buy', $gateway) }}" class="payment-method">
                            <div class="card-body text-center">
                                <img src="{{ $gateway->paymentMethod()->image() }}" style="max-height: 45px" class="img-fluid" alt="{{ $gateway->name }}">
                            </div>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
