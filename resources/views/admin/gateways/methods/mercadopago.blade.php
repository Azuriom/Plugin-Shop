<div class="mb-3">
    <label class="form-label" for="keyInput">{{ trans('shop::admin.gateways.api-key') }}</label>
    <input type="text" class="form-control @error('key') is-invalid @enderror" id="keyInput" name="key" value="{{ old('key', $gateway->data['key'] ?? '') }}" required>

    @error('key')
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror

    <div id="tokenLabel" class="form-text">@lang('shop::admin.gateways.mercadopago')</div>
</div>

<div class="mb-3">
    <label class="form-label" for="webhookSecretInput">{{ trans('shop::admin.gateways.webhook-secret') }}</label>
    <input type="text" class="form-control @error('webhook-secret') is-invalid @enderror" id="webhookSecretInput" name="webhook-secret" value="{{ old('webhook-secret', $gateway->data['webhook-secret'] ?? '') }}" aria-describedby="webhookSecretLabel">

    @error('webhook-secret')
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror

    <div id="webhookSecretLabel" class="form-text">@lang('shop::admin.gateways.mercadopago_webhook', [
        'url' => route('shop.payments.notification', 'mercadopago'),
    ])</div>
</div>
