@extends('admin.layouts.admin')

@section('title', trans('shop::admin.discounts.title-edit', ['discount' => $discount->name]))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('shop.admin.discounts.update', $discount) }}" method="POST">
                @method('PUT')

                @include('shop::admin.discounts._form')

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans('messages.actions.save') }}</button>
                <a href="{{ route('shop.admin.discounts.destroy', $discount) }}" class="btn btn-danger" data-confirm="delete"><i class="fas fa-trash"></i> {{ trans('messages.actions.delete') }}
                </a>
            </form>
        </div>
    </div>
@endsection
