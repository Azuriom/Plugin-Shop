<div class="mb-3">
    <label class="form-label" for="emailInput">{{ trans('shop::admin.gateways.skrill_email') }}</label>
    <input type="email" class="form-control @error('email') is-invalid @enderror" id="emailInput" name="email" value="{{ old('email', $gateway->data['email'] ?? '') }}" required>

    @error('email')
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="row gx-3">
    <div class="mb-3 col-md-6">
        <label class="form-label" for="websiteInput">{{ trans('shop::admin.gateways.website_id') }}</label>
        <input type="text" class="form-control @error('website_id') is-invalid @enderror" id="websiteInput" name="website_id" value="{{ old('website_id', $gateway->data['website_id'] ?? '') }}" required>

        @error('website_id')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="secretInput">{{ trans('shop::admin.gateways.skrill_secret') }}</label>
        <input type="text" class="form-control @error('secret') is-invalid @enderror" id="secretInput" name="secret" value="{{ old('secret', $gateway->data['secret'] ?? '') }}" required>

        @error('secret')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="alert alert-info" role="alert">
    <i class="bi bi-info-circle"></i> @lang('shop::admin.gateways.skrill_info')
</div>
