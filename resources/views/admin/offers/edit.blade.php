@extends('admin.layouts.admin')

@section('title', trans('shop::admin.offers.edit', ['offer' => $offer->name]))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('shop.admin.offers.update', $offer) }}" method="POST" enctype="multipart/form-data">
                @method('PUT')

                @include('shop::admin.offers._form')

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                </button>

                <a href="{{ route('shop.admin.offers.destroy', $offer) }}" class="btn btn-danger" data-confirm="delete">
                    <i class="bi bi-trash"></i> {{ trans('messages.actions.delete') }}
                </a>
            </form>
        </div>
    </div>
@endsection
