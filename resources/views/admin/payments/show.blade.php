@extends('admin.layouts.admin')

@section('title', trans('shop::admin.payments.show', ['payment' => $payment->id]))

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h5 class="card-title mb-0">
                        {{ trans('shop::admin.payments.info') }}
                    </h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li>{{ trans('shop::messages.fields.price') }}: {{ $payment->formatPrice() }}</li>

                        @if(! $payment->isWithSiteMoney())
                            <li>{{ trans('messages.fields.type') }}: {{ $payment->getTypeName() }}</li>
                            <li>
                                {{ trans('messages.fields.status') }}:
                                <span class="badge bg-{{ $payment->statusColor() }}">
                                    {{ trans('shop::admin.payments.status.'.$payment->status) }}
                                </span>
                            </li>
                            <li>{{ trans('shop::messages.fields.payment_id') }}: {{ $payment->transaction_id ?? trans('messages.unknown') }}</li>
                        @endif

                        <li>
                            {{ trans('messages.fields.user') }}:
                            <a href="{{ route('admin.users.edit', $payment->user) }}">{{ $payment->user->name }}</a>
                        </li>

                        @if($payment->subscription !== null)
                            <li>
                                {{ trans('shop::messages.fields.subscription') }}:
                                <a href="{{ route('shop.admin.subscriptions.show', $payment->subscription) }}">
                                    #{{ $payment->subscription->id }}
                                </a>
                            </li>
                        @endif

                        <li>{{ trans('messages.fields.date') }}: {{ format_date_compact($payment->created_at) }}</li>

                        @if(!$payment->coupons->isEmpty())
                            <li>
                                {{ trans('shop::messages.coupons.title') }}:

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

                        @if(!$payment->giftcards->isEmpty())
                            <li>
                                {{ trans('shop::messages.giftcards.title') }}:

                                <ul>
                                    @foreach($payment->giftcards as $giftcard)
                                        <li>
                                            <a href="{{ route('shop.admin.giftcards.edit', $giftcard) }}">
                                                {{ $giftcard->code }}
                                            </a> - {{ shop_format_amount($giftcard->pivot->amount) }}
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
                    <h5 class="card-title mb-0">
                        {{ trans('shop::admin.payments.items') }}
                    </h5>
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

                                @if(! $payment->isWithSiteMoney())
                                    <td>{{ $item->formatPrice() }}</td>
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

        @if(! $payment->items->pluck('variables')->flatten()->filter()->isEmpty())
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h5 class="card-title mb-0">
                            {{ trans('shop::admin.packages.variables') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                            <tr>
                                <th scope="col">{{ trans('shop::messages.fields.package') }}</th>
                                <th scope="col">{{ trans('messages.fields.name') }}</th>
                                <th scope="col">{{ trans('messages.fields.value') }}</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($payment->items as $item)
                                @foreach($item->variables ?? [] as $key => $value)
                                    <tr>
                                        <th scope="row">{{ $item->name }}</th>
                                        <td>{{ $key }}</td>
                                        <td>{{ $value }}</td>
                                    </tr>
                                @endforeach
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
