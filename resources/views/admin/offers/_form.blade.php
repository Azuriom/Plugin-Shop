@csrf

@php
    $translations = $offer->translations ?? [];
    $locales = array_keys($translations['name'] ?? []);
@endphp

@push('footer-scripts')
    <script>
        numberOfTranslatedElements = parseInt({{count($locales)}});

        document.addEventListener('DOMContentLoaded', function() {

            document.querySelectorAll('.translation-remove').forEach(function (el) {
                addCommandListenerToTranslations(el);
            });

            document.getElementById('addCommandButton').addEventListener('click', function () {
                let form = `
            <div>
                <div class="input-group">
                    <span class="input-group-text">Locale and translation</span>
                    <input type="text" name="translations[`+numberOfTranslatedElements+`][locale]" aria-label="en" class="form-control">
                    <input type="text" name="translations[`+numberOfTranslatedElements+`][name]" aria-label="Home" class="form-control">
                    <div class="input-group-append">
                        <button class="btn btn-outline-danger translation-remove" type="button"><i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
                `;
                addNodeToTranslationsDom(form);
            });
        });
    </script>
@endpush

<div class="form-row">
    <div class="form-group col-md-4">
        <label for="nameInput">{{ trans('messages.fields.name') }}</label>
        <div id="translations">
            @forelse ($locales as $locale)
            <div>
                <div>
                <div class="input-group">
                    <span class="input-group-text">Locale and translation</span>
                    <input type="text" value="{{ old('translations.'.$loop->index.'.locale', $locale ?? '') }}" name="translations[{{$loop->index}}][locale]" aria-label="en" class="form-control">
                    <input type="text" class="form-control" name="translations[{{$loop->index}}][name]" value="{{ old('translations.'.$loop->index.'.name', $translations['name'][$locale] ?? '') }}" required>
                    <div class="input-group-append">
                        <button class="btn btn-outline-danger translation-remove" type="button"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            </div>
            </div>
            @empty
            <div class="input-group">
                <span class="input-group-text">Locale and translation</span>
                <input type="text" value="{{ old('translations.default.locale', app()->getLocale()) }}" name="translations[default][locale]" aria-label="en" class="form-control" required>
                <input type="text" class="form-control" name="translations[default][name]" value="{{ old('translations.default.name', '') }}" required>
            </div>
            @endforelse
        </div>
        <button type="button" id="addCommandButton" class="btn btn-sm btn-success my-2">
            <i class="fas fa-plus"></i> {{ trans('messages.actions.add') }}
        </button>
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
