@extends('admin.layouts.admin')

@section('title', trans('shop::admin.purchases.title'))

@section('content')
    <form class="row row-cols-lg-auto g-3 align-items-center" action="{{ route('shop.admin.purchases.index') }}" method="GET" role="search">
        <div class="mb-3">
            <label for="searchInput" class="visually-hidden">
                {{ trans('messages.actions.search') }}
            </label>

            <div class="input-group">
                <input type="search" class="form-control" id="searchInput" name="search" value="{{ $search ?? '' }}" placeholder="{{ trans('messages.actions.search') }}">

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
                        <th scope="col">{{ trans('messages.fields.date') }}</th>
                        <th scope="col">{{ trans('messages.fields.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($purchases as $purchase)
                        <tr>
                            <th scope="row">{{ $purchase->id }}</th>
                            <td>
                                <a href="{{ route('admin.users.edit', $purchase->user) }}">{{ $purchase->user->name }}</a>
                            </td>
                            <td>{{ format_money($purchase->price) }}</td>
                            <td>{{ format_date_compact($purchase->created_at) }}</td>
                            <td>
                                <a href="{{ route('shop.admin.payments.show', $purchase) }}" class="mx-1" title="{{ trans('messages.actions.show') }}" data-bs-toggle="tooltip"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>

            {{ $purchases->withQueryString()->links() }}
        </div>
    </div>
@endsection
