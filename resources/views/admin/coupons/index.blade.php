@extends('admin.layouts.admin')

@section('title', trans('shop::admin.coupons.title'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ trans('shop::messages.fields.code') }}</th>
                        <th scope="col">{{ trans('shop::messages.fields.discount') }}</th>
                        <th scope="col">{{ trans('messages.fields.enabled') }}</th>
                        <th scope="col">{{ trans('shop::admin.coupons.active') }}</th>
                        <th scope="col">{{ trans('messages.fields.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($coupons as $coupon)
                        <tr>
                            <th scope="row">{{ $coupon->id }}</th>
                            <td>{{ $coupon->code }}</td>
                            <td>{{ $coupon->discount }} %</td>
                            <td>
                                <span class="badge badge-{{ $coupon->is_enabled ? 'success' : 'danger' }}">
                                    {{ trans_bool($coupon->is_enabled) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $coupon->isActive() ? 'success' : 'danger' }}">
                                    {{ trans_bool($coupon->isActive()) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('shop.admin.coupons.edit', $coupon) }}" class="mx-1" title="{{ trans('messages.actions.edit') }}" data-toggle="tooltip"><i class="fas fa-edit"></i></a>
                                <a href="{{ route('shop.admin.coupons.destroy', $coupon) }}" class="mx-1" title="{{ trans('messages.actions.delete') }}" data-toggle="tooltip" data-confirm="delete"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>

            <a class="btn btn-primary" href="{{ route('shop.admin.coupons.create') }}">
                <i class="fas fa-plus"></i> {{ trans('messages.actions.add') }}
            </a>
        </div>
    </div>
@endsection
