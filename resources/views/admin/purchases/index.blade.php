@extends('admin.layouts.admin')

@section('title', trans('shop::admin.purchases.title'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ trans('messages.fields.user') }}</th>
                        <th scope="col">{{ trans('shop::messages.fields.package') }}</th>
                        <th scope="col">{{ trans('shop::messages.fields.price') }}</th>
                        <th scope="col">{{ trans('shop::messages.fields.quantity') }}</th>
                        <th scope="col">{{ trans('messages.fields.date') }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($purchases as $purchase)
                        <tr>
                            <th scope="row">{{ $purchase->id }}</th>
                            <td>
                                <a href="{{ route('admin.users.edit', $purchase->user) }}">{{ $purchase->user->name }}</a>
                            </td>
                            <th>{{ $purchase->package->name }}</th>
                            <td>{{ format_money($purchase->price) }}</td>
                            <td>{{ $purchase->quantity }}</td>
                            <td>{{ format_date_compact($purchase->created_at) }}</td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>

                {{ $purchases->links() }}

            </div>
        </div>
    </div>
@endsection
