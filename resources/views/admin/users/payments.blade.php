<div class="table-responsive">
    <table class="table">
        <thead>
        <tr>
            <th scope="col">#</th>
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
                <td>{{ $payment->formatPrice() }}</td>
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
