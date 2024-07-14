@extends('admin.layouts.admin')

@section('title', trans('shop::admin.coupons.edit', ['coupon' => $coupon->code]))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('shop.admin.coupons.update', $coupon) }}" method="POST">
                @method('PUT')

                @include('shop::admin.coupons._form')

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                </button>

                <a href="{{ route('shop.admin.coupons.destroy', $coupon) }}" class="btn btn-danger" data-confirm="delete">
                    <i class="bi bi-trash"></i> {{ trans('messages.actions.delete') }}
                </a>
            </form>
        </div>
    </div>

    @if(! $payments->isEmpty())
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h5 class="card-title mb-0">
                    {{ trans('shop::messages.fields.payments') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">{{ trans('messages.fields.user') }}</th>
                            <th scope="col">{{ trans('shop::messages.fields.price') }}</th>
                            <th scope="col">{{ trans('messages.fields.status') }}</th>
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
                                <td>{{ $payment->formatPrice() }}</td>
                                <td>
                                <span class="badge bg-{{ $payment->statusColor() }}">
                                    {{ trans('shop::admin.payments.status.'.$payment->status) }}
                                </span>
                                </td>
                                <td>{{ format_date_compact($payment->created_at) }}</td>
                                <td>
                                    <a href="{{ route('shop.admin.payments.show', $payment) }}" class="mx-1" title="{{ trans('messages.actions.show') }}" data-bs-toggle="tooltip"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>

                {{ $payments->links() }}
            </div>
        </div>
    @endif
@endsection
