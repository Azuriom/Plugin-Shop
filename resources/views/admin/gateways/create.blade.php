@extends('admin.layouts.admin')

@section('title', trans('shop::admin.gateways.create'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('shop.admin.gateways.store') }}" method="POST">
                @include('shop::admin.gateways._form')

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                </button>
            </form>
        </div>
    </div>
@endsection
