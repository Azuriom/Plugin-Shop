@extends('layouts.app')

@section('title', trans('shop::messages.profile.payments'))

@section('content')
    <div class="container content">
        <h1>{{ trans('shop::messages.profile.payments') }}</h1>

        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">{{ trans('shop::messages.fields.price') }}</th>
                    <th scope="col">{{ trans('messages.fields.type') }}</th>
                    <th scope="col">{{ trans('messages.fields.status') }}</th>
                    <th scope="col">{{ trans('shop::messages.fields.payment-id') }}</th>
                    <th scope="col">{{ trans('messages.fields.date') }}</th>
                </tr>
                </thead>
                <tbody>

                @foreach($payments as $payment)
                    <tr>
                        <th scope="row">{{ $payment->id }}</th>
                        <td>{{ $payment->price }} {{ currency_display($payment->currency) }}</td>
                        <td>{{ $payment->getTypeName() }}</td>
                        <td>
                            <span class="badge badge-{{ $payment->statusColor() }}">
                                {{ trans('shop::admin.payments.payment-status.'.$payment->status) }}
                            </span>
                        </td>
                        <td>{{ $payment->transaction_id ?? trans('messages.unknown') }}</td>
                        <td>{{ format_date_compact($payment->created_at) }}</td>
                    </tr>
                @endforeach

                </tbody>
            </table>
        </div>
    </div>
@endsection
