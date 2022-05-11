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
    <h1>{{ trans('shop::messages.cart.title') }}</h1>

    <div class="card">
        <div class="card-body">
            @if(! $cart->isEmpty())
                <form action="{{ route('shop.cart.update') }}" method="POST">
                    @csrf

                    <table class="table cart-items">
                        <thead class="table-dark">
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
                                        <i class="bi bi-x-lg"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>

                    <p class="text-end mb-1">
                        <button type="submit" class="btn btn-primary btn-sm">
                            {{ trans('messages.actions.update') }}
                        </button>
                    </p>
                </form>

                <form method="POST" action="{{ route('shop.cart.clear') }}" class="text-end mb-4">
                    @csrf

                    <button type="submit" class="btn btn-danger btn-sm">
                        {{ trans('shop::messages.cart.clear') }}
                    </button>
                </form>

                <div class="row">
                    <div class="col-md-4">
                        <h5>{{ trans('shop::messages.coupons.add') }}</h5>

                        <form action="{{ route('shop.cart.coupons.add') }}" method="POST" >
                            @csrf

                            <div class="input-group mb-3 @error('code') has-validation @enderror">
                                <input type="text" class="form-control @error('code') is-invalid @enderror" placeholder="{{ trans('shop::messages.fields.code') }}" id="code" name="code" value="{{ old('code') }}">

                                <button type="submit" class="btn btn-primary">
                                    {{ trans('messages.actions.add') }}
                                </button>

                                @error('code')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </form>
                    </div>

                    @if(! $cart->coupons()->isEmpty())
                        <div class="offset-md-2 col-md-6">
                            <h5>{{ trans('shop::messages.coupons.title') }}</h5>

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
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <h5 class="text-end">
                    {{ trans('shop::messages.cart.total', ['total' => shop_format_amount($cart->total())]) }}
                </h5>

                <div class="row">
                    <div class="col-md-6">
                        <a href="{{ route('shop.home') }}" class="btn btn-secondary">
                            {{ trans('shop::messages.cart.back') }}
                        </a>
                    </div>

                    <div class="col-md-6 text-end">

                        @if(use_site_money())
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmBuyModal">
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
    </div>

    @if(use_site_money())
        <div class="modal fade" id="confirmBuyModal" tabindex="-1" role="dialog" aria-labelledby="confirmBuyLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="confirmBuyLabel">
                            {{ trans('shop::messages.cart.confirm.title') }}
                        </h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        {{ trans('shop::messages.cart.confirm.price', ['price' => shop_format_amount($cart->total())]) }}
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">
                            {{ trans('messages.actions.cancel') }}
                        </button>

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
