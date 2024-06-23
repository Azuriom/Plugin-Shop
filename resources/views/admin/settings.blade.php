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
                        <input type="checkbox" class="form-check-input" id="useSiteMoneyCheckbox" name="use_site_money" aria-describedby="moneyLabel" @checked(use_site_money())>
                        <label class="form-check-label" for="useSiteMoneyCheckbox">{{ trans('shop::admin.settings.use_site_money') }}</label>
                    </div>
                    <div id="moneyLabel" class="form-text">{{ trans('shop::admin.settings.use_site_money_info') }}</div>
                </div>

                <div class="mb-3 form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="homeSwitch" name="enable_home" data-bs-toggle="collapse" data-bs-target="#homeMessage" @checked($enableHome)>
                    <label class="form-check-label" for="homeSwitch">{{ trans('shop::admin.settings.enable_home') }}</label>
                </div>

                <div id="homeMessage" class="{{ $enableHome ? 'show' : 'collapse' }}">
                    <div class="card card-body mb-3">
                        <div class="mb-0">
                            <label class="form-label" for="homeMessage">{{ trans('shop::admin.settings.home_message') }}</label>
                            <textarea class="form-control html-editor @error('home_message') is-invalid @enderror" id="homeMessage" name="home_message" rows="5">{{ old('home_message', $homeMessage) }}</textarea>

                            @error('home_message')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row gx-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="goalInput">{{ trans('shop::messages.goal.title') }}</label>

                        <div class="input-group @error('goal') has-validation @enderror">
                            <input type="number" min="0" class="form-control @error('goal') is-invalid @enderror" id="goalInput" name="goal" value="{{ old('goal', $goal) }}">
                            <span class="input-group-text">{{ currency_display() }}</span>

                            @error('goal')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="recentPayments">{{ trans('shop::admin.settings.recent_payments') }}</label>

                        <input type="number" min="0" class="form-control @error('recent_payments') is-invalid @enderror" id="recentPayments" name="recent_payments" value="{{ old('recent_payments', $recentPayments) }}">

                        @error('recent_payments')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="topCustomerCheckbox" name="top_customer" @checked($topCustomer)>
                        <label class="form-check-label" for="topCustomerCheckbox">{{ trans('shop::admin.settings.top_customer') }}</label>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="displayAmount" name="display_amount" @checked($displayAmount)>
                        <label class="form-check-label" for="displayAmount">{{ trans('shop::admin.settings.display_amount') }}</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ trans('shop::admin.settings.commands') }}</label>

                    @include('shop::admin.commands._form', ['commands' => $commands])
                </div>

                <div class="mb-3">
                    <label class="form-label" for="webhookInput">{{ trans('shop::admin.settings.webhook') }}</label>
                    <input type="text" class="form-control @error('webhook') is-invalid @enderror" id="webhookInput" name="webhook" placeholder="https://discord.com/api/webhooks/.../..." value="{{ old('webhook', setting('shop.webhook')) }}" aria-describedby="webhookInfo">

                    @error('webhook')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror

                    <div id="webhookInfo" class="form-text">{{ trans('shop::admin.settings.webhook_info') }}</div>
                </div>

                <div class="mb-3 form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="termsSwitch" name="terms_required" data-bs-toggle="collapse" data-bs-target="#terms" @checked($termsRequired)>
                    <label class="form-check-label" for="termsSwitch">{{ trans('shop::admin.settings.terms_required') }}</label>
                </div>

                <div id="terms" class="{{ $termsRequired ? 'show' : 'collapse' }}">
                    <div class="card card-body mb-3">
                        <label class="form-label" for="termsLink">{{ trans('shop::admin.settings.terms') }}</label>
                        <input type="text" class="form-control @error('terms') is-invalid @enderror" id="termsLink" name="terms" value="{{ old('terms', setting('shop.required_terms')) }}">

                        @error('terms')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror

                        <div id="termsLabel" class="form-text">@lang('shop::admin.settings.terms_info')</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                </button>
            </form>

        </div>
    </div>
@endsection
