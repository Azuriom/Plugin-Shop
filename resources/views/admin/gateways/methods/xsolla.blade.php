<div class="form-row">
    <div class="form-group col-md-6">
        <label for="keyInput">{{ trans('shop::admin.gateways.api-key') }}</label>
        <input type="text" class="form-control @error('api-key') is-invalid @enderror" id="keyInput" name="api-key" value="{{ old('api-key', $gateway->data['api-key'] ?? '') }}" required>

        @error('api-key')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="form-group col-md-6">
        <label for="keyInput">{{ trans('shop::admin.gateways.secret-key') }}</label>
        <input type="text" class="form-control @error('secret-key') is-invalid @enderror" id="keyInput" name="secret-key" value="{{ old('secret-key', $gateway->data['secret-key'] ?? '') }}" required>

        @error('secret-key')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="keyInput">{{ trans('shop::admin.gateways.merchant-id') }}</label>
        <input type="text" class="form-control @error('merchant-id') is-invalid @enderror" id="keyInput" name="merchant-id" value="{{ old('merchant-id', $gateway->data['merchant-id'] ?? '') }}" required>

        @error('merchant-id')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="form-group col-md-6">
        <label for="keyInput">{{ trans('shop::admin.gateways.project-id') }}</label>
        <input type="text" class="form-control @error('project-id') is-invalid @enderror" id="keyInput" name="project-id" value="{{ old('project-id', $gateway->data['project-id'] ?? '') }}" required>

        @error('project-id')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>


<div class="alert alert-info" role="alert">
    <i class="fas fa-info-circle"></i> @lang('shop::admin.gateways.xsolla', ['url' => route('shop.payments.notification', 'xsolla')])
</div>
