<div class="form-row">
    <div class="form-group col-md-6">
        <label for="keyInput">{{ trans('shop::admin.gateways.client-id') }}</label>
        <input type="text" class="form-control @error('client-id') is-invalid @enderror" id="keyInput" name="client-id" value="{{ old('client-id', $gateway->data['client-id'] ?? '') }}" required>

        @error('client-id')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="form-group col-md-6">
        <label for="keyInput">{{ trans('shop::admin.gateways.secret-key') }}</label>
        <input type="text" class="form-control @error('secret') is-invalid @enderror" id="keyInput" name="secret" value="{{ old('secret', $gateway->data['secret'] ?? '') }}" required>

        @error('secret')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>