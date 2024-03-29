@extends('admin.layouts.admin')

@section('title', trans('shop::admin.coupons.create'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('shop.admin.coupons.store') }}" method="POST">
                @include('shop::admin.coupons._form')

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                </button>
            </form>
        </div>
    </div>
@endsection
