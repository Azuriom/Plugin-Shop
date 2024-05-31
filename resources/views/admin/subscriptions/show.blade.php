@extends('admin.layouts.admin')

@section('title', trans('shop::admin.subscriptions.show', ['subscription' => $subscription->id]))

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h5 class="card-title mb-0">
                        {{ trans('shop::admin.subscriptions.info') }}
                    </h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li>
                            {{ trans('messages.fields.status') }}:
                            <span class="badge bg-{{ $subscription->statusColor() }}">
                                {{ trans('shop::admin.subscriptions.status.'.$subscription->status) }}
                            </span>
                        </li>

                        <li>{{ trans('shop::messages.fields.price') }}: {{ $subscription->formatPrice() }}</li>

                        @if(! $subscription->isWithSiteMoney())
                            <li>{{ trans('messages.fields.type') }}: {{ $subscription->getTypeName() }}</li>
                            <li>{{ trans('shop::messages.fields.subscription_id') }}: {{ $subscription->subscription_id ?? trans('messages.unknown') }}</li>
                        @endif

                        <li>
                            {{ trans('messages.fields.user') }}:
                            <a href="{{ route('admin.users.edit', $subscription->user) }}">
                                {{ $subscription->user->name }}
                            </a>
                        </li>

                        @if($subscription->package !== null)
                            <li>
                                {{ trans('shop::messages.fields.package') }}:
                                <a href="{{ route('shop.admin.packages.edit', $subscription->package) }}">
                                    {{ $subscription->package->name }}
                                </a>
                            </li>
                        @endif

                        @if($subscription->ends_at !== null)
                            <li>
                                {{ trans('shop::messages.fields.renewal_date') }}:
                                {{ format_date_compact($subscription->ends_at) }}
                            </li>
                        @endif

                        <li>{{ trans('messages.fields.date') }}: {{ format_date_compact($subscription->created_at) }}</li>
                    </ul>

                    @if($subscription->isActive() && ! $subscription->isCanceled())
                        <a href="{{ route('shop.admin.subscriptions.destroy', $subscription) }}" class="btn btn-danger" data-confirm="delete">
                            <i class="bi bi-x-circle"></i> {{ trans('messages.actions.cancel') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h5 class="card-title mb-0">
                        {{ trans('shop::admin.payments.title') }}
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">{{ trans('shop::messages.fields.price') }}</th>
                            @if(! $subscription->isWithSiteMoney())
                                <th scope="col">{{ trans('shop::messages.fields.payment_id') }}</th>
                            @endif
                            <th scope="col">{{ trans('messages.fields.date') }}</th>
                            <th scope="col">{{ trans('messages.fields.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($subscription->payments as $payment)
                            <tr>
                                <td>{{ $payment->formatPrice() }}</td>
                                @if(! $subscription->isWithSiteMoney())
                                    <td>{{ $payment->transaction_id }}</td>
                                @endif
                                <td>{{ format_date_compact($payment->created_at) }}</td>
                                <td>
                                    <a href="{{ route('shop.admin.payments.show', $payment) }}" class="mx-1" title="{{ trans('messages.actions.show') }}" data-bs-toggle="tooltip"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
