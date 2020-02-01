<div class="form-row">
    <div class="form-group col-md-6">
        <label for="keyInput">{{ trans('shop::admin.gateways.public-key') }}</label>
        <input type="text" class="form-control @error('public-key') is-invalid @enderror" id="keyInput" name="public-key" value="{{ old('public-key', $gateway->data['public-key'] ?? '') }}" required placeholder="nano_...">

        @error('public-key')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>