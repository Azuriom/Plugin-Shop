<div class="form-row">
    <div class="form-group col-md-7">
        <label for="keyInput">{{ trans('shop::admin.gateways.api-key') }}</label>
        <input type="text" class="form-control @error('key') is-invalid @enderror" id="keyInput" name="key" value="{{ old('key', $gateway->data['key'] ?? '') }}" required placeholder="psc_..">

        @error('key')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="form-group col-md-5">
        <label for="environmentInput">{{ trans('shop::admin.gateways.env') }}</label>

        <select class="custom-select @error('environment') is-invalid @enderror" id="environmentInput" name="environment">
            @foreach($type->environments() as $env)
                <option value="{{ $env }}" @if(($gateway->data['environment'] ?? '') === $env) selected @endif>{{ $env }}</option>
            @endforeach
        </select>

        @error('environment')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="alert alert-info" role="alert">
    <i class="fas fa-info-circle"></i> @lang('shop::admin.gateways.paysafecard-info')
</div>
