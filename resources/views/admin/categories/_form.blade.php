@csrf

@php
    $translations = $category->translations ?? [];
    $locales = array_keys($translations['name'] ?? []);
@endphp

@push('footer-scripts')
    <script>
        numberOfTranslatedElements = parseInt({{count($locales)}});

        document.addEventListener('DOMContentLoaded', function() {

            document.querySelectorAll('.command-remove').forEach(function (el) {
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
                        <button class="btn btn-outline-danger command-remove" type="button"><i class="fas fa-times"></i>
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

<div class="form-group">
    <label for="translations">{{ trans('messages.fields.name') }}</label>
    <div id="translations">
        @forelse ($locales as $locale)
        <div>
            <div>
            <div class="input-group">
                <span class="input-group-text">Locale and translation</span>
                <input type="text" value="{{ old('translations.'.$loop->index.'.locale', $locale ?? '') }}" name="translations[{{$loop->index}}][locale]" aria-label="en" class="form-control">
                <input type="text" class="form-control" name="translations[{{$loop->index}}][name]" value="{{ old('translations.'.$loop->index.'.name', $translations['name'][$locale] ?? '') }}" required>
                <div class="input-group-append">
                    <button class="btn btn-outline-danger command-remove" type="button"><i class="fas fa-times"></i></button>
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

<div class="form-group custom-control custom-switch">
    <input type="checkbox" class="custom-control-input" id="cumulatePurchasesSwitch" name="cumulate_purchases" @if(old('cumulate_purchases', $category->cumulate_purchases ?? false)) checked @endif>
    <label class="custom-control-label" for="cumulatePurchasesSwitch">{{ trans('shop::admin.categories.cumulate_purchases') }}</label>
</div>

<div class="form-group custom-control custom-switch">
    <input type="checkbox" class="custom-control-input" id="enableSwitch" name="is_enabled" @if(old('is_enabled', $category->is_enabled ?? true)) checked @endif>
    <label class="custom-control-label" for="enableSwitch">{{ trans('shop::admin.categories.enable') }}</label>
</div>
