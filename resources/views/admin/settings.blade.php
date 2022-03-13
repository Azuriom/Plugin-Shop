@extends('admin.layouts.admin')

@include('admin.elements.editor')

@section('title', trans('shop::admin.settings.title'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">

            <form action="{{ route('shop.admin.settings') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="currencySelect">{{ trans('shop::messages.fields.currency') }}</label>

                    <select class="form-select @error('currency') is-invalid @enderror" id="currencySelect" name="currency">
                        @foreach($currencies as $code => $name)
                            <option value="{{ $code }}" @selected($currentCurrency === $code)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="useSiteMoneyCheckbox" name="use_site_money" @checked(use_site_money())>
                        <label class="form-check-label" for="useSiteMoneyCheckbox">{{ trans('shop::admin.settings.use_site_money') }}</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="homeMessage">{{ trans('shop::admin.settings.home_message') }}</label>
                    <textarea class="form-control html-editor @error('home-message') is-invalid @enderror" id="homeMessage" name="home-message" rows="5">{{ old('home-message', $homeMessage) }}</textarea>

                    @error('home-message')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="goalInput">{{ trans('shop::messages.goal.title') }}</label>

                    <div class="input-group @error('goal') has-validation @enderror">
                        <input type="number" min="0" class="form-control @error('goal') is-invalid @enderror" id="goalInput" name="goal" value="{{ old('goal', $goal) }}">
                        <span class="input-group-text">{{ currency_display() }}</span>

                        @error('goal')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ trans('shop::admin.settings.commands') }}</label>

                    @include('shop::admin.elements.commands', ['commands' => $commands])
                </div>

                <div class="mb-3">
                    <label class="form-label" for="webhookInput">{{ trans('shop::admin.settings.webhook') }}</label>
                    <input type="text" class="form-control @error('webhook') is-invalid @enderror" id="webhookInput" name="webhook" placeholder="https://discord.com/api/webhooks/.../..." value="{{ old('webhook', setting('shop.webhook')) }}" aria-describedby="webhookInfo">

                    @error('webhook')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror

                    <small id="webhookInfo" class="form-text">{{ trans('shop::admin.settings.webhook_info') }}</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                </button>
            </form>

        </div>
    </div>
@endsection
