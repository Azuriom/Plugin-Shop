<div class="form-row">
    <div class="form-group col-md-6">
        <label for="keyInput">{{ trans('shop::admin.gateways.api-key') }}</label>
        <input type="text" class="form-control @error('key') is-invalid @enderror" id="keyInput" name="key" value="{{ old('key', $gateway->data['key'] ?? '') }}" required>

        @error('key')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="form-group col-md-6">
        <label for="serviceIdInput">{{ trans('shop::admin.gateways.service-id') }}</label>
        <input type="text" class="form-control @error('service-id') is-invalid @enderror" id="serviceIdInput" name="service-id" value="{{ old('service-id', $gateway->data['service-id'] ?? '') }}" required>

        @error('service-id')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>