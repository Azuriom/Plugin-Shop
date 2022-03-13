@extends('admin.layouts.admin')

@section('title', trans('shop::admin.gateways.title'))

@section('content')
    @if(! $gateways->isEmpty())
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h5 class="card-title mb-0">
                    {{ trans('shop::admin.gateways.current') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">

                    @foreach($gateways as $gateway)
                        <div class="col-md-3">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header">{{ $gateway->name }}</div>
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <img src="{{ $gateway->paymentMethod()->image() }}" style="max-height: 45px" class="img-fluid" alt="{{ $gateway->name }}">
                                    </div>

                                    <a href="{{ route('shop.admin.gateways.edit', $gateway) }}" class="btn btn-primary">
                                        <i class="bi bi-pencil-square"></i> {{ trans('messages.actions.edit') }}
                                    </a>
                                    <a href="{{ route('shop.admin.gateways.destroy', $gateway) }}" class="btn btn-danger" data-confirm="delete">
                                        <i class="bi bi-trash"></i> {{ trans('messages.actions.delete') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    @endif

    @if(! $paymentMethods->isEmpty())
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h5 class="card-title mb-0">
                    {{ trans('shop::admin.gateways.add') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="typeSelect">{{ trans('messages.fields.type') }}</label>
                    <select class="form-select @error('type') is-invalid @enderror" id="typeSelect" name="type" required>
                        @foreach($paymentMethods as $paymentMethod)
                            <option value="{{ route('shop.admin.gateways.create', $paymentMethod) }}">{{ $paymentMethod }}</option>
                        @endforeach
                    </select>
                </div>

                <a href="#" onclick="this.href = document.getElementById('typeSelect').value" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> {{ trans('messages.actions.add') }}
                </a>
            </div>
        </div>
    @endif
@endsection
