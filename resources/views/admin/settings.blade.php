@extends('admin.layouts.admin')

@section('title', trans('shop::admin.settings.title'))

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

                <div class="form-group">
                    <label for="goalInput">{{ trans('shop::messages.month-goal') }}</label>

                    <div class="input-group">
                        <input type="number" min="0" class="form-control @error('goal') is-invalid @enderror" id="goalInput" name="goal" value="{{ old('goal', $goal) }}">
                        <div class="input-group-append">
                            <span class="input-group-text">{{ currency_display() }}</span>
                        </div>

                        @error('goal')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>{{ trans('shop::admin.settings.commands') }}</label>

                    @include('shop::admin.elements.commands', ['commands' => $commands])
                </div>

                <div class="form-group">
                    <label for="webhookInput">{{ trans('shop::admin.settings.webhook') }}</label>
                    <input type="text" class="form-control @error('webhook') is-invalid @enderror" id="webhookInput" name="webhook" placeholder="https://discordapp.com/api/webhooks/.../..." value="{{ old('webhook', setting('shop.webhook')) }}" aria-describedby="webhookInfo">

                    @error('webhook')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror

                    <small id="webhookInfo" class="form-text">{{ trans('shop::admin.settings.webhook-info') }}</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans('messages.actions.save') }}
                </button>
            </form>

        </div>
    </div>
@endsection
