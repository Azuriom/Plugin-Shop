<div class="form-group">
    <label for="emailInput">{{ trans('shop::admin.gateways.paypal-email') }}</label>
    <input type="email" class="form-control @error('email') is-invalid @enderror" id="emailInput" name="email" value="{{ old('email', $gateway->data['email'] ?? '') }}" required placeholder="hello@world.com">

    @error('email')
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>
