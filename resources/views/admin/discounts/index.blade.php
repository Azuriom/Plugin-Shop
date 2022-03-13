@extends('admin.layouts.admin')

@section('title', trans('shop::admin.discounts.title'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ trans('messages.fields.name') }}</th>
                        <th scope="col">{{ trans('shop::messages.fields.discount') }}</th>
                        <th scope="col">{{ trans('shop::admin.discounts.active') }}</th>
                        <th scope="col">{{ trans('messages.fields.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($discounts as $discount)
                        <tr>
                            <th scope="row">{{ $discount->id }}</th>
                            <td>{{ $discount->name }}</td>
                            <td>{{ $discount->discount }} %</td>
                            <td>
                                <span class="badge bg-{{ $discount->isActive() ? 'success' : 'danger' }}">
                                    {{ trans_bool($discount->isActive()) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('shop.admin.discounts.edit', $discount) }}" class="mx-1" title="{{ trans('messages.actions.edit') }}" data-bs-toggle="tooltip"><i class="bi bi-pencil-square"></i></a>
                                <a href="{{ route('shop.admin.discounts.destroy', $discount) }}" class="mx-1" title="{{ trans('messages.actions.delete') }}" data-bs-toggle="tooltip" data-confirm="delete"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>

            <a class="btn btn-primary" href="{{ route('shop.admin.discounts.create') }}">
                <i class="bi bi-plus-lg"></i> {{ trans('messages.actions.add') }}
            </a>
        </div>
    </div>
@endsection
