@extends('admin.layouts.admin')

@section('title', trans('shop::admin.payments.title-show', ['payment' => $payment->id]))

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ trans('shop::admin.payments.info') }}</h6>
                </div>
                <div class="card-body">
                    <ul>
                        @if($payment->gateway_type !== 'azuriom')
                            <li>{{ trans('shop::messages.fields.price') }}: {{ $payment->price }} {{ currency_display($payment->currency) }}</li>
                            <li>{{ trans('messages.fields.type') }}: {{ $payment->getTypeName() }}</li>
                            <li>
                                {{ trans('shop::admin.payments.fields.status') }}:
                                <span class="badge badge-{{ $payment->statusColor() }}">
                                    {{ trans('shop::admin.payments.payment-status.'.$payment->status) }}
                                </span>
                            </li>
                            <li>{{ trans('shop::admin.payments.fields.payment-id') }}: {{ $payment->transaction_id ?? trans('messages.unknown') }}</li>
                        @else
                            <li>{{ trans('shop::messages.fields.price') }}: {{ format_money($payment->price) }}</li>
                        @endif

                        <li>
                            {{ trans('messages.fields.user') }}
                            <a href="{{ route('admin.users.edit', $payment->user) }}">{{ $payment->user->name }}</a>
                        </li>
                        <li>{{ trans('messages.fields.date') }}: {{ format_date_compact($payment->created_at, true) }}</li>

                        @if(!$payment->coupons->isEmpty())
                            <li>
                                {{ trans('shop::messages.cart.coupons') }}:

                                <ul>
                                    @foreach($payment->coupons as $coupon)
                                        <li>
                                            <a href="{{ route('shop.admin.coupons.edit', $coupon) }}">
                                                {{ $coupon->code }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ trans('shop::admin.payments.items') }}</h6>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th scope="col">{{ trans('messages.fields.name') }}</th>
                            <th scope="col">{{ trans('shop::messages.fields.price') }}</th>
                            <th scope="col">{{ trans('shop::messages.fields.quantity') }}</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($payment->items as $item)
                            <tr>
                                <th scope="row">{{ $item->buyable_id }}</th>
                                <td>{{ $item->name }}</td>

                                @if($payment->gateway_type !== 'azuriom')
                                    <td>{{ $item->price }} {{ currency_display($payment->currency) }}</td>
                                @else
                                    <td>{{ format_money($item->price) }}</td>
                                @endif

                                <td>{{ $item->quantity }}</td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
