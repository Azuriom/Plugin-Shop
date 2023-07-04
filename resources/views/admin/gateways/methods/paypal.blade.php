<div class="mb-3">
    <label class="form-label" for="emailInput">{{ trans('shop::admin.gateways.paypal_email') }}</label>
    <input type="email" class="form-control @error('email') is-invalid @enderror" id="emailInput" name="email" value="{{ old('email', $gateway->data['email'] ?? '') }}" required placeholder="hello@world.com">

    @error('email')
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="alert alert-info" role="alert">
    <i class="bi bi-info-circle"></i> @lang('shop::admin.gateways.paypal_info')
</div>
