<div class="form-row">
    <div class="form-group col-md-6">
        <label for="keyInput">{{ trans('shop::admin.gateways.public-key') }}</label>
        <input type="text" class="form-control @error('public-key') is-invalid @enderror" id="keyInput" name="public-key" value="{{ old('public-key', $gateway->data['public-key'] ?? '') }}" required placeholder="pk_...">

        @error('public-key')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="form-group col-md-6">
        <label for="keyInput">{{ trans('shop::admin.gateways.secret-key') }}</label>
        <input type="text" class="form-control @error('secret-key') is-invalid @enderror" id="keyInput" name="secret-key" value="{{ old('secret-key', $gateway->data['secret-key'] ?? '') }}" required placeholder="sk_...">

        @error('secret-key')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="endpointInput">Endpoint secret</label>
    <input type="text" class="form-control @error('endpoint-secret') is-invalid @enderror" id="endpointInput" name="endpoint-secret" value="{{ old('endpoint-secret', $gateway->data['endpoint-secret'] ?? '') }}" placeholder="whsec_...">

    @error('endpoint-secret')
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="alert alert-info" role="alert">
    <i class="fas fa-info-circle"></i> @lang('shop::admin.gateways.stripe-info', ['url' => route('shop.payments.notification', 'stripe')])
</div>
