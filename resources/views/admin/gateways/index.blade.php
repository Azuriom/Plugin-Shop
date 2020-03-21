@extends('admin.layouts.admin')

@section('title', trans('shop::admin.gateways.title'))

@section('content')
    @if(! $gateways->isEmpty())
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">{{ trans('shop::admin.gateways.subtitle-current') }}</h6>
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
                                        <i class="fas fa-edit"></i> {{ trans('messages.actions.edit') }}
                                    </a>
                                    <a href="{{ route('shop.admin.gateways.destroy', $gateway) }}" class="btn btn-danger" data-confirm="delete">
                                        <i class="fas fa-trash"></i> {{ trans('messages.actions.delete') }}
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
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">{{ trans('shop::admin.gateways.subtitle-add') }}</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="typeSelect">{{ trans('messages.fields.type') }}</label>
                    <select class="custom-select @error('type') is-invalid @enderror" id="typeSelect" name="type" required>
                        @foreach($paymentMethods as $paymentMethod)
                            <option value="{{ route('shop.admin.gateways.create', $paymentMethod) }}">{{ $paymentMethod }}</option>
                        @endforeach
                    </select>
                </div>

                <a href="#" onclick="this.href = document.getElementById('typeSelect').value" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ trans('messages.actions.add') }}
                </a>
            </div>
        </div>
    @endif
@endsection
