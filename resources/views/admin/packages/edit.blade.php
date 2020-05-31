@extends('admin.layouts.admin')

@section('title', trans('shop::admin.packages.title-edit', ['package' => $package->id]))

@include('admin.elements.editor')

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('shop.admin.packages.update', $package) }}" method="POST" enctype="multipart/form-data">
                @method('PUT')

                @include('shop::admin.packages._form')

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans('messages.actions.save') }}
                </button>

                <a href="{{ route('shop.admin.packages.destroy', $package) }}" class="btn btn-danger" data-confirm="delete">
                    <i class="fas fa-trash"></i> {{ trans('messages.actions.delete') }}
                </a>
            </form>
        </div>
    </div>
@endsection
