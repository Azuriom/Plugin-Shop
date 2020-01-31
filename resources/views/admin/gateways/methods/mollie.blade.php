<div class="form-group">
    <label for="keyInput">{{ trans('shop::admin.gateways.api-key') }}</label>
    <input type="text" class="form-control @error('key') is-invalid @enderror" id="keyInput" name="key" value="{{ old('key', $gateway->data['key'] ?? '') }}" required>

    @error('key')
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>