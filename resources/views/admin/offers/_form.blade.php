@csrf

<div class="form-row">
    <div class="form-group col-md-4">
        <label for="nameInput">{{ trans('messages.fields.name') }}</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="nameInput" name="name" value="{{ old('name', $offer->name ?? '') }}" required>

        @error('name')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="form-group col-md-4">
        <label for="moneyInput">{{ trans('messages.fields.money') }}</label>
        <div class="input-group">
            <input type="number" min="0" step="0.01" max="999999" class="form-control @error('money') is-invalid @enderror" id="moneyInput" name="money" value="{{ old('money', $offer->money ?? '') }}" required>
            <div class="input-group-append">
                <span class="input-group-text">{{ money_name() }}</span>
            </div>

            @error('money')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>

    <div class="form-group col-md-4">
        <label for="priceInput">{{ trans('shop::messages.fields.price') }}</label>

        <div class="input-group mb-3">
            <input type="number" min="0" step="0.01" max="999999" class="form-control @error('price') is-invalid @enderror" id="priceInput" name="price" value="{{ old('price', $offer->price ?? '') }}" required>
            <div class="input-group-append">
                <span class="input-group-text">{{ currency_display() }}</span>
            </div>

            @error('price')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label>{{ trans('shop::messages.fields.gateways') }}</label>

    <div class="card card-body pb-0">
        @foreach($gateways as $gateway)
            <div class="form-group custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="gateways{{ $gateway->id }}" name="gateways[{{ $gateway->id }}]" @if(isset($offer) && $offer->gateways->contains($gateway)) checked @endif>
                <label class="custom-control-label" for="gateways{{ $gateway->id }}">{{ $gateway->name }}</label>
            </div>
        @endforeach
    </div>

    @error('gateways')
    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="form-group custom-control custom-switch">
    <input type="checkbox" class="custom-control-input" id="enableSwitch" name="is_enabled" @if($offer->is_enabled ?? true) checked @endif>
    <label class="custom-control-label" for="enableSwitch">{{ trans('shop::admin.offers.enable') }}</label>
</div>
