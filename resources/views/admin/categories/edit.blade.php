@extends('admin.layouts.admin')

@section('title', trans('shop::admin.categories.title-edit', ['category' => $category->name]))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('shop.admin.categories.update', $category) }}" method="POST">
                @method('PUT')

                @include('shop::admin.categories._form')

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ trans('messages.actions.save') }}</button>
                <a href="{{ route('shop.admin.categories.destroy', $category) }}" class="btn btn-danger" data-confirm="delete"><i class="fas fa-trash"></i> {{ trans('messages.actions.delete') }}</a>
            </form>
        </div>
    </div>
@endsection
