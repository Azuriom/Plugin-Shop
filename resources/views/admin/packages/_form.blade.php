@include('shop::admin.elements.select')

@csrf

@php
    $translations = $package->translations ?? [];
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
            <div class="form-group">
                <label for="translationInput-`+numberOfTranslatedElements+`">Translation</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="translationInput-`+numberOfTranslatedElements+`" name="translations[`+numberOfTranslatedElements+`][locale]" value="" required>
                    <div class="input-group-append">
                        <button class="btn btn-outline-danger translation-remove" type="button"><i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="nameInput-`+numberOfTranslatedElements+`">{{ trans('messages.fields.name') }}</label>
                <input type="text" class="form-control" id="nameInput-`+numberOfTranslatedElements+`" name="translations[`+numberOfTranslatedElements+`][name]" value="" required>
            </div>

            <div class="form-group">
                <label for="short_descriptionInput-`+numberOfTranslatedElements+`">{{ trans('messages.fields.short-description') }}</label>
                <input type="text" class="form-control" id="short_descriptionInput-`+numberOfTranslatedElements+`" name="translations[`+numberOfTranslatedElements+`][short_description]" value="" required>
            </div>

            <div class="form-group">
                <label for="textArea-`+numberOfTranslatedElements+`">{{ trans('messages.fields.description') }}</label>
                <textarea class="form-control" id="textArea-`+numberOfTranslatedElements+`" name="translations[`+numberOfTranslatedElements+`][description]" rows="5"></textarea>
            </div>
            `;

            addNodeToTranslationsDom(form);
            });
        });
    </script>
@endpush

<div id="translations">
    @forelse ($locales as $locale)
    <div>
        <div class="form-group">
            <label for="translationInput-{{$loop->index}}">Translation</label>
            <div class="input-group">
                <input type="text" class="form-control" id="translationInput-{{$loop->index}}" name="translations[{{$loop->index}}][locale]" value="{{ old('translations.'.$loop->index.'.locale', $locale ?? '') }}" required>
                <div class="input-group-append">
                    <button class="btn btn-outline-danger translation-remove" type="button"><i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    
        <div class="form-group">
            <label for="nameInput-{{$loop->index}}">{{ trans('messages.fields.name') }}</label>
            <input type="text" class="form-control @error('name-'.$loop->index) is-invalid @enderror" id="nameInput-{{$loop->index}}" name="translations[{{$loop->index}}][name]" value="{{ old('translations.'.$loop->index.'.name', $translations['name'][$locale] ?? '') }}" required>
    
            @error('name-'.$loop->index)
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    
        <div class="form-group">
            <label for="short_descriptionInput-{{$loop->index}}">{{ trans('messages.fields.short-description') }}</label>
            <input type="text" class="form-control @error('short_description-'.$loop->index) is-invalid @enderror" id="short_descriptionInput-{{$loop->index}}" name="translations[{{$loop->index}}][short_description]" value="{{ old('translations.'.$loop->index.'.short_description', $translations['short_description'][$locale] ?? '') }}" required>
    
            @error('short_description-'.$loop->index)
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    
        <div class="form-group">
            <label for="textArea-{{$loop->index}}">{{ trans('messages.fields.description') }}</label>
            <textarea class="form-control html-editor @error('description-'.$loop->index) is-invalid @enderror" id="textArea-{{$loop->index}}" name="translations[{{$loop->index}}][description]" rows="5">{{ old('translations.'.$loop->index.'.description', $translations['description'][$locale] ?? '') }}</textarea>
    
            @error('description-'.$loop->index)
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
@empty
    <div class="form-group">
        <label for="nameInput">{{ trans('messages.fields.name') }}</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="nameInput" name="name" value="{{ old('name', $package->name ?? '') }}" required>
    
        @error('name')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="shortDescriptionInput">{{ trans('messages.fields.short-description') }}</label>
        <input type="text" class="form-control @error('short_description') is-invalid @enderror" id="shortDescriptionInput" name="short_description" value="{{ old('short_description', $package->short_description ?? '') }}" required>
    
        @error('short_description')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="descriptionArea">{{ trans('messages.fields.description') }}</label>
        <textarea class="form-control html-editor @error('description') is-invalid @enderror" id="descriptionArea" name="description" rows="5">{{ old('description', $package->description ?? '') }}</textarea>
    
        @error('description')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
    @endforelse
</div>

<button type="button" id="addCommandButton" class="btn btn-sm btn-success my-2">
    <i class="fas fa-plus"></i> {{ trans('messages.actions.add') }}
</button>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="categorySelect">{{ trans('shop::messages.fields.category') }}</label>
        <select class="custom-select @error('category_id') is-invalid @enderror" id="categorySelect" name="category_id" required>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @if(isset($package) && $category->is($package->category)) selected @endif>
                    {{ $category->name }}
                </option>

                @foreach($category->categories as $subCategory)
                    <option value="{{ $subCategory->id }}" @if(isset($package) && $subCategory->is($package->category)) selected @endif>
                        {{ $subCategory->name }}
                    </option>
                @endforeach
            @endforeach
        </select>

        @error('category_id')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="form-group col-md-6">
        <label for="priceInput">{{ trans('shop::messages.fields.price') }}</label>

        <div class="input-group">
            <input type="number" min="0" step="0.01" max="999999" class="form-control @error('price') is-invalid @enderror" id="priceInput" name="price" value="{{ old('price', $package->price ?? '') }}" required>
            <div class="input-group-append">
                <span class="input-group-text">{{ shop_active_currency() }}</span>
            </div>

            @error('price')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="roleSelect">{{ trans('shop::messages.fields.role') }}</label>
        <select class="custom-select @error('role_id') is-invalid @enderror" id="roleSelect" name="role_id">
            <option value="">{{ trans('messages.none') }}</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}" @if(isset($package) && $role->is($package->role)) selected @endif>{{ $role->name }}</option>
            @endforeach
        </select>

        @error('role_id')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="form-group col-md-6">
        <label for="requiredPackagesSelect">{{ trans('shop::messages.fields.required_packages') }}</label>

        <select class="custom-select @error('required_packages') is-invalid @enderror" id="requiredPackagesSelect" name="required_packages[]" multiple>
            @foreach($categories as $category)
                <optgroup label="{{ $category->name }}">
                    @foreach($category->packages as $categoryPackage)
                        @if(! isset($package) || ! $categoryPackage->is($package))
                            <option value="{{ $categoryPackage->id }}" @if(isset($package) && optional($package->required_packages)->contains($categoryPackage->id)) selected @endif>{{ $categoryPackage->name }}</option>
                        @endif
                    @endforeach
                </optgroup>
            @endforeach
        </select>

        @error('required_packages')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="imageInput">{{ trans('messages.fields.image') }}</label>
        <div class="custom-file">
            <input type="file" class="custom-file-input  @error('image') is-invalid @enderror" id="imageInput" name="image" accept=".jpg,.jpeg,.jpe,.png,.gif,.bmp,.svg,.webp" data-image-preview="imagePreview">
            <label class="custom-file-label" data-browse="{{ trans('messages.actions.browse') }}">{{ trans('messages.actions.choose-file') }}</label>

            @error('image')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        <img src="{{ ($package->image ?? false) ? $package->imageUrl() : '#' }}" class="mt-2 img-fluid rounded img-preview {{ ($package->image ?? false) ? '' : 'd-none' }}" alt="Image" id="imagePreview">
    </div>

    <div class="form-group col-md-6">
        <label for="userLimitInput">{{ trans('shop::messages.fields.user_limit') }}</label>

        <input type="number" min="0" step="1" class="form-control @error('user_limit') is-invalid @enderror" id="userLimitInput" name="user_limit" value="{{ old('user_limit', $package->user_limit ?? '') }}">

        @error('user_limit')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="form-group">
    <label>{{ trans('shop::messages.fields.commands') }}</label>

    @include('shop::admin.elements.commands', ['commands' => $package->commands ?? []])
</div>

<div class="form-group">
    <label for="serversSelect">{{ trans('shop::messages.fields.servers') }}</label>

    <select class="custom-select @error('servers') is-invalid @enderror" id="serversSelect" name="servers[]" multiple>
        @foreach($servers as $server)
            <option value="{{ $server->id }}" @if(isset($package) && $package->servers->contains($server) ?? false) selected @endif>{{ $server->name }}</option>
        @endforeach
    </select>

    @error('servers')
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="form-group custom-control custom-switch">
    <input type="checkbox" class="custom-control-input" id="needOnlineSwitch" name="need_online" @if($package->need_online ?? true) checked @endif>
    <label class="custom-control-label" for="needOnlineSwitch">{{ trans('shop::admin.packages.need-online') }}</label>
</div>

<div class="form-group custom-control custom-switch">
    <input type="checkbox" class="custom-control-input" id="quantitySwitch" name="has_quantity" @if($package->has_quantity ?? true) checked @endif>
    <label class="custom-control-label" for="quantitySwitch">{{ trans('shop::admin.packages.enable-quantity') }}</label>
</div>

<div class="form-group custom-control custom-switch">
    <input type="checkbox" class="custom-control-input" id="enableSwitch" name="is_enabled" @if($package->is_enabled ?? true) checked @endif>
    <label class="custom-control-label" for="enableSwitch">{{ trans('shop::admin.packages.enable') }}</label>
</div>
