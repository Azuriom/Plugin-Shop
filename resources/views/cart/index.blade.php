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

        /* Balance slider */
        .balance-block {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px 24px;
            margin-top: 20px;
        }

        .balance-block .balance-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 700;
            font-size: .95rem;
            margin-bottom: 14px;
        }

        .balance-block .badge-balance {
            background: rgba(255, 165, 0, 0.15);
            border: 1px solid rgba(255, 165, 0, 0.3);
            border-radius: 20px;
            padding: 3px 12px;
            font-size: .82rem;
            font-weight: 600;
            color: #ffa500;
        }

        .balance-block input[type=range] {
            width: 100%;
            cursor: pointer;
            accent-color: #ffa500;
        }

        .balance-range-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 4px;
            font-size: .78rem;
            color: rgba(255, 255, 255, .4);
        }

        .balance-summary {
            display: flex;
            gap: 12px;
            justify-content: space-between;
            margin-top: 14px;
        }

        .balance-summary .bs-item {
            flex: 1;
            background: rgba(255, 255, 255, .04);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: .85rem;
        }

        .balance-summary .bs-item .bs-label {
            margin-bottom: 3px;
            font-size: .78rem;
            color: rgba(255, 255, 255, .45);
        }

        .balance-summary .bs-item .bs-value {
            font-weight: 700;
        }

        .bs-value-balance {
            color: #ffa500;
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
                                    <input type="number" min="0" max="{{ $cartItem->maxQuantity() }}" size="5"
                                           class="form-control form-control-sm d-inline-block"
                                           name="quantities[{{ $cartItem->itemId }}]"
                                           value="{{ $cartItem->quantity }}"
                                           aria-label="{{ trans('shop::messages.fields.quantity') }}"
                                           required @if(!$cartItem->hasQuantity()) readonly @endif>
                                </td>
                                <td>
                                    <a href="{{ route('shop.cart.remove', $cartItem->id) }}"
                                       class="btn btn-sm btn-danger"
                                       title="{{ trans('messages.actions.delete') }}">
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

                    <form action="{{ route('shop.cart.coupons.add') }}" method="POST">
                        @csrf

                        <div class="input-group mb-3 @error('coupon') has-validation @enderror">
                            <input type="text"
                                   class="form-control @error('coupon') is-invalid @enderror"
                                   id="coupon" name="coupon"
                                   value="{{ old('coupon') }}"
                                   placeholder="{{ trans('shop::messages.fields.code') }}"
                                   required>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i> {{ trans('messages.actions.add') }}
                            </button>

                            @error('coupon')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
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
                                        <form action="{{ route('shop.cart.coupons.remove', $coupon) }}"
                                              method="POST"
                                              class="d-inline-block">
                                            @csrf

                                            <button type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    title="{{ trans('messages.actions.delete') }}">
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

                    <form action="{{ route('shop.cart.giftcards.add') }}" method="POST">
                        @csrf

                        <div class="input-group mb-3 @error('giftcard') has-validation @enderror">
                            <input type="text"
                                   class="form-control @error('giftcard') is-invalid @enderror"
                                   id="giftcard" name="giftcard"
                                   value="{{ old('giftcard') }}"
                                   placeholder="{{ trans('shop::messages.fields.code') }}"
                                   required>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i> {{ trans('messages.actions.add') }}
                            </button>

                            @error('giftcard')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
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
                                        <form action="{{ route('shop.cart.giftcards.remove', $giftcard) }}"
                                              method="POST"
                                              class="d-inline-block">
                                            @csrf

                                            <button type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    title="{{ trans('messages.actions.delete') }}">
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

            {{-- Balance slider — only shown when site money is enabled and the user has a positive balance --}}
            @if(use_site_money() && auth()->check() && ! $cart->isEmpty() && $userBalance > 0)
                @php
                    $cartTotal = $cart->payableTotal();
                    $maxSpend = min($userBalance, $cartTotal);
                    $currentSpend = $cart->getBalanceAmount();
                    $gatewayDue = max($cartTotal - $currentSpend, 0);
                @endphp

                <div class="balance-block"
                     id="balance-slider-wrap"
                     data-url="{{ route('shop.cart.balance') }}"
                     data-csrf="{{ csrf_token() }}"
                     data-cart-total="{{ $cartTotal }}">

                    <div class="balance-title">
                        <span>
                            <i class="bi bi-wallet2 me-2" style="color: #ffa500;"></i>
                            {{ trans('shop::messages.cart.use_balance') }}
                        </span>
                        <span class="badge-balance" id="bs-badge">
                            {{ shop_format_amount($currentSpend, true) }}
                        </span>
                    </div>

                    <input type="range"
                           id="balance-slider"
                           min="0"
                           max="{{ $maxSpend }}"
                           step="1"
                           value="{{ $currentSpend }}">

                    <div class="balance-range-labels">
                        <span>0</span>
                        <span>
                            {{ trans('shop::messages.cart.your_balance') }}:
                            <strong style="color: #ffa500;">{{ shop_format_amount($userBalance, true) }}</strong>
                        </span>
                        <span>{{ shop_format_amount($maxSpend, true) }}</span>
                    </div>

                    <div class="balance-summary">
                        <div class="bs-item">
                            <div class="bs-label">{{ trans('shop::messages.cart.from_balance') }}</div>
                            <div class="bs-value bs-value-balance" id="bs-from-balance">
                                {{ shop_format_amount($currentSpend, true) }}
                            </div>
                        </div>
                        <div class="bs-item">
                            <div class="bs-label">{{ trans('shop::messages.cart.via_gateway') }}</div>
                            <div class="bs-value" id="bs-via-gateway">
                                {{ shop_format_amount($gatewayDue) }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form @if(! use_site_money()) action="{{ route('shop.payments.payment') }}" @endif class="mt-3">
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
                        <button type="button"
                                class="btn btn-primary ms-auto"
                                data-bs-toggle="modal"
                                data-bs-target="#confirmBuyModal">
                            {{ trans('shop::messages.buy') }}
                        </button>
                    @else
                        @if($emailRequired)
                            <input type="email"
                                   class="form-group @error('email') is-invalid @enderror"
                                   id="email" name="email"
                                   value="{{ old('email') }}"
                                   placeholder="{{ trans('auth.email') }}"
                                   required>
                        @endif

                        <button type="submit" class="btn btn-primary ms-auto">
                            <i class="bi bi-cart-check"></i> {{ trans('shop::messages.cart.checkout') }}
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if(use_site_money())
        @php
            $mTotal = $cart->payableTotal();
            $mSpend = $cart->getBalanceAmount();
            $mDue = max($mTotal - $mSpend, 0);
            $mFullyCovered = $mDue < 0.01;
        @endphp

        <div class="modal fade" id="confirmBuyModal" tabindex="-1" role="dialog" aria-labelledby="confirmBuyLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="confirmBuyLabel">
                            {{ trans('shop::messages.cart.confirm.title') }}
                        </h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body" id="confirm-modal-body">
                        @if($mSpend > 0)
                            <p class="mb-1">
                                {{ trans('shop::messages.cart.from_balance') }}:
                                <strong style="color: #ffa500;">{{ shop_format_amount($mSpend, true) }}</strong>
                            </p>
                            @if($mFullyCovered)
                                <p class="mb-0 text-success fw-bold">
                                    {{ trans('shop::messages.cart.fully_covered') }}
                                </p>
                            @else
                                <p class="mb-0">
                                    {{ trans('shop::messages.cart.via_gateway') }}:
                                    <strong>{{ shop_format_amount($mDue) }}</strong>
                                </p>
                            @endif
                        @else
                            {{ trans('shop::messages.cart.confirm.price', ['price' => shop_format_amount($mTotal)]) }}
                        @endif
                    </div>

                    <form class="modal-footer" method="POST" action="{{ route('shop.cart.payment') }}">
                        @csrf

                        @include('shop::cart._terms', ['terms' => $terms])

                        <div class="ms-auto">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">
                                {{ trans('messages.actions.cancel') }}
                            </button>

                            <button class="btn btn-primary" id="confirm-pay-btn" type="submit">
                                {{ trans('shop::messages.cart.pay') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('footer-scripts')
    <script>
        (function () {
            var wrap = document.getElementById('balance-slider-wrap');

            if (!wrap) {
                return;
            }

            var slider = document.getElementById('balance-slider');
            var badge = document.getElementById('bs-badge');
            var fromBalance = document.getElementById('bs-from-balance');
            var viaGateway = document.getElementById('bs-via-gateway');
            var confirmBtn = document.getElementById('confirm-pay-btn');
            var url = wrap.dataset.url;
            var csrf = wrap.dataset.csrf;
            var cartTotal = parseFloat(wrap.dataset.cartTotal);
            var moneyName = @json(money_name(1));
            var debounceTimer = null;

            function formatBalance(value) {
                return value + ' ' + moneyName;
            }

            function formatGateway(value) {
                return formatBalance(parseFloat(value).toFixed(2));
            }

            function updateConfirmButton(spend, due) {
                if (!confirmBtn) {
                    return;
                }

                if (spend > 0 && due < 0.01) {
                    confirmBtn.textContent = '{{ trans('shop::messages.cart.pay_with_balance') }}';
                } else if (spend > 0) {
                    confirmBtn.textContent = '{{ trans('shop::messages.cart.pay_remaining') }}: ' + formatGateway(due);
                } else {
                    confirmBtn.textContent = '{{ trans('shop::messages.cart.pay') }}';
                }
            }

            slider.addEventListener('input', function () {
                var spend = parseFloat(slider.value);
                var due = Math.max(cartTotal - spend, 0);

                badge.textContent = formatBalance(spend);
                fromBalance.textContent = formatBalance(spend);
                viaGateway.textContent = formatGateway(due);
                updateConfirmButton(spend, due);

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({amount: spend}),
                    }).catch(function (error) {
                        console.error('Failed to store balance amount:', error);
                    });
                }, 300);
            });
        }());
    </script>
@endpush
