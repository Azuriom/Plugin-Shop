@extends('admin.layouts.admin')

@section('title', trans('shop::admin.packages.title-create'))

@include('admin.elements.editor')

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('shop.admin.packages.store') }}" method="POST" enctype="multipart/form-data">
                @include('shop::admin.packages._form')

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans('messages.actions.save') }}
                </button>
            </form>
        </div>
    </div>
@endsection
