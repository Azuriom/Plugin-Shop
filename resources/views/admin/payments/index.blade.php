@extends('admin.layouts.admin')

@section('title', trans('shop::admin.payments.title'))

@section('content')
    <form class="form-inline mb-3" action="{{ route('shop.admin.payments.index') }}" method="GET">
        <div class="mb-3 mb-2">
            <label for="searchInput" class="visually-hidden">
                {{ trans('messages.actions.search') }}
            </label>

            <div class="input-group">
                <input type="text" class="form-control" id="searchInput" name="search" value="{{ $search ?? '' }}" placeholder="{{ trans('messages.actions.search') }}">

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i>
                </button>
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
                        <th scope="col">{{ trans('messages.fields.status') }}</th>
                        <th scope="col">{{ trans('shop::messages.fields.payment_id') }}</th>
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
                                <span class="badge bg-{{ $payment->statusColor() }}">
                                    {{ trans('shop::admin.payments.status.'.$payment->status) }}
                                </span>
                            </td>
                            <td>{{ $payment->transaction_id ?? trans('messages.unknown') }}</td>
                            <td>{{ format_date_compact($payment->created_at) }}</td>
                            <td>
                                <a href="{{ route('shop.admin.payments.show', $payment) }}" class="mx-1" title="{{ trans('messages.actions.show') }}" data-bs-toggle="tooltip"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>

            {{ $payments->withQueryString()->links() }}

            <a href="{{ route('shop.admin.payments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> {{ trans('messages.actions.add') }}
            </a>
        </div>
    </div>
@endsection
