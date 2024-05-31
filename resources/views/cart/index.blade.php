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
                            <i class="bi bi-check-lg"></i> {{ trans('messages.actions.update') }}
                        </button>
                    </p>
                </form>

                <form method="POST" action="{{ route('shop.cart.clear') }}" class="text-end mb-4">
                    @csrf

                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i> {{ trans('shop::messages.cart.clear') }}
                    </button>
                </form>
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-exclamation-circle"></i> {{ trans('shop::messages.cart.empty') }}
                </div>
            @endif

            <div class="row">
                <div class="col-md-4">
                    <h5>{{ trans('shop::messages.coupons.add') }}</h5>

                    <form action="{{ route('shop.cart.coupons.add') }}" method="POST" >
                        @csrf

                        <div class="input-group mb-3 @error('code') has-validation @enderror">
                            <input type="text" class="form-control @error('code') is-invalid @enderror" placeholder="{{ trans('shop::messages.fields.code') }}" id="code" name="code" value="{{ old('code') }}">

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i> {{ trans('messages.actions.add') }}
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
                <div class="col-md-4">
                    <h5>{{ trans('shop::messages.giftcards.add') }}</h5>

                    <form action="{{ route('shop.cart.giftcards.add') }}" method="POST" >
                        @csrf

                        <div class="input-group mb-3 @error('code') has-validation @enderror">
                            <input type="text" class="form-control @error('code') is-invalid @enderror" placeholder="{{ trans('shop::messages.fields.code') }}" id="code" name="code" value="{{ old('code') }}">

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i> {{ trans('messages.actions.add') }}
                            </button>

                            @error('code')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </form>
                </div>

                @if(! $cart->giftcards()->isEmpty())
                    <div class="offset-md-2 col-md-6">
                        <h5>{{ trans('shop::messages.giftcards.title') }}</h5>

                        <table class="table coupons">
                            <thead>
                            <tr>
                                <th scope="col">{{ trans('messages.fields.name') }}</th>
                                <th scope="col">{{ trans('shop::messages.fields.discount') }}</th>
                                <th scope="col">{{ trans('messages.fields.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($cart->giftcards() as $giftcard)
                                <tr>
                                    <th scope="row">{{ $giftcard->code }}</th>
                                    <td>{{ shop_format_amount($giftcard->balance) }}</td>
                                    <td>
                                        <form action="{{ route('shop.cart.giftcards.remove', $giftcard) }}" method="POST" class="d-inline-block">
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

                        <h5 class="text-end">
                            {{ trans('shop::messages.cart.payable_total', ['total' => shop_format_amount($cart->payableTotal())]) }}
                        </h5>
                    </div>
                @endif
            </div>

            <form @if(! use_site_money()) action="{{ route('shop.payments.payment') }}" @endif>
                @if(! use_site_money())
                    <div class="d-flex justify-content-end">
                        @include('shop::cart._terms', ['terms' => $terms])
                    </div>
                @endif

                <div class="d-flex">
                    <a href="{{ route('shop.home') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> {{ trans('shop::messages.cart.back') }}
                    </a>

                    @if(use_site_money())
                        <button type="button" class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#confirmBuyModal">
                            {{ trans('shop::messages.buy') }}
                        </button>
                    @else
                        <button type="submit" class="btn btn-primary ms-auto">
                            <i class="bi bi-cart-check"></i> {{ trans('shop::messages.cart.checkout') }}
                        </button>
                    @endif
                </div>
            </form>
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
                        {{ trans('shop::messages.cart.confirm.price', ['price' => shop_format_amount($cart->payableTotal())]) }}
                    </div>

                    <form class="modal-footer" method="POST" action="{{ route('shop.cart.payment') }}">
                        @csrf

                        @include('shop::cart._terms', ['terms' => $terms])

                        <div class="ms-auto">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">
                                {{ trans('messages.actions.cancel') }}
                            </button>

                            <button class="btn btn-primary" type="submit">
                                {{ trans('shop::messages.cart.pay') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
