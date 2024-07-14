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

    @if($type->supportsSubscriptions())
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> @lang('shop::admin.gateways.subscription')
        </div>
    @else
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i> @lang('shop::admin.gateways.no_subscription')
        </div>
    @endif

    <div class="alert alert-info" role="alert">
        <i class="bi bi-info-circle"></i> @lang('shop::admin.gateways.info')
    </div>
@endsection
