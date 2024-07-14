<div class="row gx-3">
    <div class="mb-3 col-md-6">
        <label class="form-label" for="keyInput">{{ trans('shop::admin.gateways.client-id') }}</label>
        <input type="text" class="form-control @error('client-id') is-invalid @enderror" id="keyInput" name="client-id" value="{{ old('client-id', $gateway->data['client-id'] ?? '') }}" required>

        @error('client-id')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="keyInput">{{ trans('shop::admin.gateways.secret-key') }}</label>
        <input type="text" class="form-control @error('secret') is-invalid @enderror" id="keyInput" name="secret" value="{{ old('secret', $gateway->data['secret'] ?? '') }}" required>

        @error('secret')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="row gx-3">
    <div class="mb-3 col-md-6">
        <label class="form-label" for="webhookInput">{{ trans('shop::admin.gateways.webhook_id') }}</label>
        <input type="text" class="form-control @error('webhook_id') is-invalid @enderror" id="webhookInput" name="webhook_id" value="{{ old('webhook_id', $gateway->data['webhook_id'] ?? '') }}" required>

        @error('webhook_id')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="environmentInput">{{ trans('shop::admin.gateways.env') }}</label>

        <select class="form-select @error('environment') is-invalid @enderror" id="environmentInput" name="environment">
            @foreach(['live', 'sandbox'] as $env)
                <option value="{{ $env }}" @selected(($gateway->data['environment'] ?? 'live') === $env)>
                    {{ ucwords($env) }}
                </option>
            @endforeach
        </select>

        @error('environment')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="alert alert-info" role="alert">
    <i class="bi bi-info-circle"></i> @lang('shop::admin.gateways.paypal_checkout', [
        'url' => route('shop.payments.notification', 'paypal-checkout'),
        'events' => 'BILLING.SUBSCRIPTION.ACTIVATED, CUSTOMER.DISPUTE.CREATED, PAYMENT.CAPTURE.REFUNDED, PAYMENT.CAPTURE.REVERSED, PAYMENT.SALE.COMPLETED',
    ])
</div>
