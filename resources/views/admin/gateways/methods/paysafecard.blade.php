<div class="row gx-3">
    <div class="mb-3 col-md-7">
        <label class="form-label" for="keyInput">{{ trans('shop::admin.gateways.api-key') }}</label>
        <input type="text" class="form-control @error('key') is-invalid @enderror" id="keyInput" name="key" value="{{ old('key', $gateway->data['key'] ?? '') }}" required placeholder="psc_..">

        @error('key')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-5">
        <label class="form-label" for="environmentInput">{{ trans('shop::admin.gateways.env') }}</label>

        <select class="form-select @error('environment') is-invalid @enderror" id="environmentInput" name="environment">
            @foreach($type->environments() as $env)
                <option value="{{ $env }}" @selected(($gateway->data['environment'] ?? '') === $env)>{{ $env }}</option>
            @endforeach
        </select>

        @error('environment')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="alert alert-info" role="alert">
    <i class="bi bi-info-circle"></i> @lang('shop::admin.gateways.paysafecard_info')
</div>
