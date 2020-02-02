@extends('admin.layouts.admin')

@section('title', {{ trans('shop::admin.settings.title') }})

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">

            <form action="{{ route('shop.admin.settings') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="currencySelect">{{ trans('shop::messages.fields.currency') }}</label>

                    <select class="custom-select @error('currency') is-invalid @enderror" id="currencySelect" name="currency">
                        @foreach($currencies as $code => $name)
                            <option value="{{ $code }}" @if($currentCurrency === $code) selected @endif>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="useSiteMoneyCheckbox" name="use-site-money" @if(use_site_money()) checked @endif>
                        <label class="custom-control-label" for="useSiteMoneyCheckbox">{{ trans('shop::admin.settings.use-site-money') }}</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans('messages.actions.save') }}
                </button>

            </form>

        </div>
    </div>
@endsection
