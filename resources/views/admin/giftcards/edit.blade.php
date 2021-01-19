@extends('admin.layouts.admin')

@section('title', trans('shop::admin.giftcards.title-edit', ['giftcard' => $giftcard->name]))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('shop.admin.giftcards.update', $giftcard) }}" method="POST">
                @method('PUT')

                @include('shop::admin.giftcards._form')

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans('messages.actions.save') }}</button>
                <a href="{{ route('shop.admin.giftcards.destroy', $giftcard) }}" class="btn btn-danger" data-confirm="delete"><i class="fas fa-trash"></i> {{ trans('messages.actions.delete') }}
                </a>
            </form>
        </div>
    </div>
@endsection
