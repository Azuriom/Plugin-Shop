@extends('layouts.app')

@section('title', trans('shop::messages.cart.title'))

@section('content')
    <div class="container content">
        @if(! $cart->isEmpty())

            <table class="table">
                <thead>
                <tr>
                    <th scope="col">{{ trans('messages.fields.name') }}</th>
                    <th scope="col">{{ trans('shop::messages.fields.price') }}</th>
                    <th scope="col">{{ trans('shop::messages.fields.quantity') }}</th>
                    <th scope="col">{{ trans('messages.fields.action') }}</th>
                </tr>
                </thead>
                <tbody>

                @foreach($cart->content() as $cartItem)
                    <tr>
                        <th scope="row">{{ $cartItem->name() }}</th>
                        <td>{{ shop_format_amount($cartItem->total()) }}</td>
                        <td>{{ $cartItem->quantity }}</td>
                        <td>
                            <a href="{{ route('shop.cart.remove', $cartItem->id) }}" class="btn btn-sm btn-danger">
                                {{ trans('shop::messages.cart.remove') }}
                            </a>
                        </td>
                    </tr>
                @endforeach

                </tbody>
            </table>

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

            <a href="{{ route('shop.home') }}" class="btn btn-info">
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
