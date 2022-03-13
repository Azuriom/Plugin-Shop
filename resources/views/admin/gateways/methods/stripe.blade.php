@include('shop::admin.elements.select')

<div class="row g-3">
    <div class="mb-3 col-md-6">
        <label class="form-label" for="keyInput">{{ trans('shop::admin.gateways.public-key') }}</label>
        <input type="text" class="form-control @error('public-key') is-invalid @enderror" id="keyInput" name="public-key" value="{{ old('public-key', $gateway->data['public-key'] ?? '') }}" required placeholder="pk_...">

        @error('public-key')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="keyInput">{{ trans('shop::admin.gateways.secret-key') }}</label>
        <input type="text" class="form-control @error('secret-key') is-invalid @enderror" id="keyInput" name="secret-key" value="{{ old('secret-key', $gateway->data['secret-key'] ?? '') }}" required placeholder="sk_...">

        @error('secret-key')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="endpointInput">Endpoint secret</label>
        <input type="text" class="form-control @error('endpoint-secret') is-invalid @enderror" id="endpointInput" name="endpoint-secret" value="{{ old('endpoint-secret', $gateway->data['endpoint-secret'] ?? '') }}" placeholder="whsec_...">

        @error('endpoint-secret')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="methodsSelect">{{ trans('shop::admin.gateways.methods') }}</label>

        <select class="form-select @error('methods') is-invalid @enderror" id="methodsSelect" name="methods[]" multiple aria-describedby="methodsInfo">
            @foreach(\Azuriom\Plugin\Shop\Payment\Method\StripeMethod::PAYMENT_METHODS as $id => $name)
                <option value="{{ $id }}" @selected(in_array($id, $gateway->data['methods'] ?? [], true))>{{ $name }}</option>
            @endforeach
        </select>

        @error('methods')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror

        <small id="methodsInfo" class="form-text">
            @lang('shop::admin.gateways.methods-info', ['docs' => 'https://stripe.com/payments/payment-methods-guide'])
        </small>
    </div>
</div>

<div class="alert alert-info" role="alert">
    <i class="bi bi-info-circle"></i> @lang('shop::admin.gateways.stripe_info', ['url' => route('shop.payments.notification', 'stripe')])
</div>
