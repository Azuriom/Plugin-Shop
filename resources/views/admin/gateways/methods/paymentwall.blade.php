<div class="form-row">
    <div class="form-group col-md-6">
        <label for="keyInput">{{ trans('shop::admin.gateways.public-key') }}</label>
        <input type="text" class="form-control @error('public-key') is-invalid @enderror" id="keyInput" name="public-key" value="{{ old('public-key', $gateway->data['public-key'] ?? '') }}" required>

        @error('public-key')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="form-group col-md-6">
        <label for="keyInput">{{ trans('shop::admin.gateways.private-key') }}</label>
        <input type="text" class="form-control @error('private-key') is-invalid @enderror" id="keyInput" name="private-key" value="{{ old('private-key', $gateway->data['private-key'] ?? '') }}" required>

        @error('private-key')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="alert alert-info" role="alert">
    <i class="fas fa-info-circle"></i> @lang('shop::admin.gateways.paymentwall-info', ['url' => route('shop.payments.notification', 'paymentwall')])
</div>
