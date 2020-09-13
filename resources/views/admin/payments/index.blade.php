@extends('admin.layouts.admin')

@section('title', trans('shop::admin.payments.title'))

@section('content')
    <form class="form-inline mb-3" action="{{ route('shop.admin.payments.index') }}" method="GET">
        <div class="form-group mb-2">
            <label for="searchInput" class="sr-only">{{ trans('messages.actions.search') }}</label>

            <div class="input-group">
                <input type="text" class="form-control" id="searchInput" name="search" value="{{ $search ?? '' }}" placeholder="{{ trans('messages.actions.search') }}">

                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search fa-sm"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="card shadow mb-4">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ trans('messages.fields.user') }}</th>
                        <th scope="col">{{ trans('shop::messages.fields.price') }}</th>
                        <th scope="col">{{ trans('messages.fields.type') }}</th>
                        <th scope="col">{{ trans('shop::admin.payments.fields.status') }}</th>
                        <th scope="col">{{ trans('shop::admin.payments.fields.payment-id') }}</th>
                        <th scope="col">{{ trans('messages.fields.date') }}</th>
                        <th scope="col">{{ trans('messages.fields.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($payments as $payment)
                        <tr>
                            <th scope="row">{{ $payment->id }}</th>
                            <td>
                                <a href="{{ route('admin.users.edit', $payment->user) }}">{{ $payment->user->name }}</a>
                            </td>
                            <td>{{ $payment->price }} {{ currency_display($payment->currency) }}</td>
                            <td>{{ $payment->getTypeName() }}</td>
                            <td>
                                <span class="badge badge-{{ $payment->statusColor() }}">
                                    {{ trans('shop::admin.payments.payment-status.'.$payment->status) }}
                                </span>
                            </td>
                            <td>{{ $payment->transaction_id ?? trans('messages.unknown') }}</td>
                            <td>{{ format_date_compact($payment->created_at) }}</td>
                            <td>
                                <a href="{{ route('shop.admin.payments.show', $payment) }}" class="mx-1" title="{{ trans('messages.actions.show') }}" data-toggle="tooltip"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>

            {{ $payments->withQueryString()->links() }}
        </div>
    </div>
@endsection
