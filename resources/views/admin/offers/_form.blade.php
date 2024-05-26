@csrf

<div class="row gx-3">
    <div class="mb-3 col-md-4">
        <label class="form-label" for="nameInput">{{ trans('messages.fields.name') }}</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="nameInput" name="name" value="{{ old('name', $offer->name ?? '') }}" required>

        @error('name')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label" for="moneyInput">{{ trans('messages.fields.money') }}</label>
        <div class="input-group @error('money') has-validation @enderror">
            <input type="number" min="0" step="0.01" max="999999" class="form-control @error('money') is-invalid @enderror" id="moneyInput" name="money" value="{{ old('money', $offer->money ?? '') }}" required>
            <span class="input-group-text">{{ money_name() }}</span>

            @error('money')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label" for="priceInput">{{ trans('shop::messages.fields.price') }}</label>

        <div class="input-group mb-3 @error('price') has-validation @enderror">
            <input type="number" min="0" step="0.01" max="999999" class="form-control @error('price') is-invalid @enderror" id="priceInput" name="price" value="{{ old('price', $offer->price ?? '') }}" required>
            <span class="input-group-text">{{ currency_display() }}</span>

            @error('price')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
</div>

<div class="mb-3">
    <label class="form-label" for="imageInput">{{ trans('messages.fields.image') }}</label>
    <input type="file" class="form-control @error('image') is-invalid @enderror" id="imageInput" name="image" accept=".jpg,.jpeg,.jpe,.png,.gif,.bmp,.svg,.webp" data-image-preview="imagePreview">

    @error('image')
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror

    <img src="{{ ($offer->image ?? false) ? $offer->imageUrl() : '#' }}" class="mt-2 img-fluid rounded img-preview {{ ($offer->image ?? false) ? '' : 'd-none' }}" alt="Image" id="imagePreview">
</div>

<div class="mb-3">
    <label class="form-label">{{ trans('shop::messages.fields.gateways') }}</label>

    <div class="card card-body pb-0">
        <div class="row">
            @foreach($gateways as $gateway)
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="gateways{{ $gateway->id }}" name="gateways[]" value="{{ $gateway->id }}" @checked(isset($offer) && $offer->gateways->contains($gateway))>
                        <label class="form-check-label" for="gateways{{ $gateway->id }}">{{ $gateway->name }}</label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @error('gateways')
    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="enableSwitch" name="is_enabled" @checked($offer->is_enabled ?? true)>
    <label class="form-check-label" for="enableSwitch">{{ trans('shop::admin.offers.enable') }}</label>
</div>
