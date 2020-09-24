<div class="form-row">
    <div class="form-group col-md-5">
        <label for="keyInput">{{ trans('shop::admin.gateways.api-key') }}</label>
        <input type="text" class="form-control @error('key') is-invalid @enderror" id="keyInput" name="key" value="{{ old('key', $gateway->data['key'] ?? '') }}" required>

        @error('key')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="form-group col-md-5">
        <label for="serviceIdInput">{{ trans('shop::admin.gateways.service-id') }}</label>
        <input type="text" class="form-control @error('service-id') is-invalid @enderror" id="serviceIdInput" name="service-id" value="{{ old('service-id', $gateway->data['service-id'] ?? '') }}" required>

        @error('service-id')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="form-group col-md-2">
        <label for="countrySelect">{{ trans('shop::admin.gateways.country') }}</label>
        <select class="custom-select @error('country') is-invalid @enderror" id="countrySelect" name="country" required>
            @foreach($type->countries() as $code => $country)
                <option value="{{ $code }}" @if(old('country', $gateway->data['country'] ?? 'US') === $code) selected @endif>{{ $country }}</option>
            @endforeach
        </select>

        @error('country')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>
