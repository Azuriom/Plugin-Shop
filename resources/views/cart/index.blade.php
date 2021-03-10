@extends('layouts.app')

@section('title', trans('shop::messages.cart.title'))

@push('styles')
    <style>
        .cart-items thead th {
            width: 40%;
        }

        .cart-items tbody td {
            width: 15%;
        }
    </style>
@endpush

@section('content')
    <div class="container content">
        <h1>{{ trans('shop::messages.cart.title') }}</h1>

        @if(! $cart->isEmpty())

            <form action="{{ route('shop.cart.update') }}" method="POST">
                @csrf

                <table class="table cart-items">
                    <thead>
                    <tr>
                        <th scope="col">{{ trans('messages.fields.name') }}</th>
                        <th scope="col">{{ trans('shop::messages.fields.price') }}</th>
                        <th scope="col">{{ trans('shop::messages.fields.total') }}</th>
                        <th scope="col">{{ trans('shop::messages.fields.quantity') }}</th>
                        <th scope="col">{{ trans('messages.fields.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($cart->content() as $cartItem)
                        <tr>
                            <th scope="row">{{ $cartItem->name() }}</th>
                            <td>{{ shop_format_amount($cartItem->price()) }}</td>
                            <td>{{ shop_format_amount($cartItem->total()) }}</td>
                            <td>
                                <input type="number" min="0" max="{{ $cartItem->maxQuantity() }}" size="5" class="form-control form-control-sm d-inline-block" name="quantities[{{ $cartItem->itemId }}]" value="{{ $cartItem->quantity }}" aria-label="{{ trans('shop::messages.fields.quantity') }}" required @if(!$cartItem->hasQuantity()) readonly @endif>
                            </td>
                            <td>
                                <a href="{{ route('shop.cart.remove', $cartItem->id) }}" class="btn btn-sm btn-danger" title="{{ trans('messages.actions.delete') }}">
                                    <i class="fas fa-times fa-fw"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>

                <p class="text-right mb-4">
                    <button type="submit" class="btn btn-primary btn-sm">
                        {{ trans('messages.actions.update') }}
                    </button>
                </p>
            </form>

            <div class="row">
                <div class="col-md-8">
                    <h5>{{ trans('shop::messages.cart.coupons') }}</h5>

                    <table class="table coupons">
                        <thead>
                        <tr>
                            <th scope="col">{{ trans('messages.fields.name') }}</th>
                            <th scope="col">{{ trans('shop::messages.fields.discount') }}</th>
                            <th scope="col">{{ trans('messages.fields.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($cart->coupons() as $coupon)
                            <tr>
                                <th scope="row">{{ $coupon->code }}</th>
                                <td>{{ $coupon->is_fixed ? shop_format_amount($coupon->discount) : $coupon->discount.' %' }}</td>
                                <td>
                                    <form action="{{ route('shop.cart.coupons.remove', $coupon) }}" method="POST" class="d-inline-block">
                                        @csrf

                                        <button type="submit" class="btn btn-sm btn-danger" title="{{ trans('messages.actions.delete') }}">
                                            <i class="fas fa-times fa-fw"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>

                <div class="col-md-4">
                    <h5>{{ trans('shop::messages.cart.add-coupon') }}</h5>

                    <form action="{{ route('shop.cart.coupons.add') }}" method="POST" class="form-inline mb-3">
                        @csrf

                        <div class="form-group">
                            <input type="text" class="form-control @error('code') is-invalid @enderror mx-2" placeholder="{{ trans('shop::messages.fields.code') }}" id="code" name="code" value="{{ old('code') }}">

                            @error('code')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">{{ trans('messages.actions.add') }}</button>
                    </form>
                </div>
            </div>

            <p class="font-weight-bold text-right mb-4">
                <span class="border border-dark p-2 rounded">
                    {{ trans('shop::messages.cart.total', ['total' => shop_format_amount($cart->total())]) }}
                </span>
            </p>

            <div class="row">
                <div class="col-md-6">
                    <a href="{{ route('shop.home') }}" class="btn btn-info">
                        {{ trans('shop::messages.cart.back') }}
                    </a>
                </div>

                <div class="col-md-6 text-right">
                    <form method="POST" action="{{ route('shop.cart.clear') }}" class="d-inline-block">
                        @csrf

                        <button type="submit" class="btn btn-danger">
                            {{ trans('shop::messages.cart.clear') }}
                        </button>
                    </form>

                    @if(use_site_money())
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#confirmBuyModal">
                            {{ trans('shop::messages.buy') }}
                        </button>
                    @else
                        <a href="{{ route('shop.payments.payment') }}" class="btn btn-primary">
                            {{ trans('shop::messages.cart.checkout') }}
                        </a>
                    @endif
                </div>
            </div>
        @else
            <div class="alert alert-warning" role="alert">
                {{ trans('shop::messages.cart.empty') }}
            </div>

            <a href="{{ route('shop.home') }}" class="btn btn-primary">
                {{ trans('shop::messages.cart.back') }}
            </a>
        @endif
    </div>

    @if(use_site_money())
        <div class="modal fade" id="confirmBuyModal" tabindex="-1" role="dialog" aria-labelledby="confirmBuyLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="confirmBuyLabel">{{ trans('shop::messages.cart.pay-confirm-title') }}</h2>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        {{ trans('shop::messages.cart.pay-confirm', ['price' => shop_format_amount($cart->total())]) }}
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">{{ trans('messages.actions.cancel') }}</button>

                        <form method="POST" action="{{ route('shop.cart.payment') }}">
                            @csrf

                            <button class="btn btn-primary" type="submit">
                                {{ trans('shop::messages.cart.pay') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
