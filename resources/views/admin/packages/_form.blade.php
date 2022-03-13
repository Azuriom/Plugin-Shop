@include('shop::admin.elements.select')

@csrf

<div class="mb-3">
    <label class="form-label" for="nameInput">{{ trans('messages.fields.name') }}</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="nameInput" name="name" value="{{ old('name', $package->name ?? '') }}" required>

    @error('name')
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label" for="shortDescriptionInput">{{ trans('messages.fields.short_description') }}</label>
    <input type="text" class="form-control @error('short_description') is-invalid @enderror" id="shortDescriptionInput" name="short_description" value="{{ old('short_description', $package->short_description ?? '') }}" required>

    @error('short_description')
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label" for="descriptionArea">{{ trans('messages.fields.description') }}</label>
    <textarea class="form-control html-editor @error('description') is-invalid @enderror" id="descriptionArea" name="description" rows="5">{{ old('description', $package->description ?? '') }}</textarea>

    @error('description')
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="row g-3">
    <div class="mb-3 col-md-6">
        <label class="form-label" for="categorySelect">{{ trans('shop::messages.fields.category') }}</label>
        <select class="form-select @error('category_id') is-invalid @enderror" id="categorySelect" name="category_id" required>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(isset($package) && $category->is($package->category))>
                    {{ $category->name }}
                </option>

                @foreach($category->categories as $subCategory)
                    <option value="{{ $subCategory->id }}" @selected(isset($package) && $subCategory->is($package->category))>
                        {{ $subCategory->name }}
                    </option>
                @endforeach
            @endforeach
        </select>

        @error('category_id')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="priceInput">{{ trans('shop::messages.fields.price') }}</label>

        <div class="input-group @error('price') has-validation @enderror">
            <input type="number" min="0" step="0.01" max="999999" class="form-control @error('price') is-invalid @enderror" id="priceInput" name="price" value="{{ old('price', $package->price ?? '') }}" required>
            <span class="input-group-text">{{ shop_active_currency() }}</span>

            @error('price')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="roleSelect">{{ trans('shop::messages.fields.role') }}</label>
        <select class="form-select @error('role_id') is-invalid @enderror" id="roleSelect" name="role_id">
            <option value="">{{ trans('messages.none') }}</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}" @selected(isset($package) && $role->is($package->role))>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>

        @error('role_id')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="userLimitInput">{{ trans('shop::messages.fields.user_limit') }}</label>

        <input type="number" min="0" step="1" class="form-control @error('user_limit') is-invalid @enderror" id="userLimitInput" name="user_limit" value="{{ old('user_limit', $package->user_limit ?? '') }}">

        @error('user_limit')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="requiredRoleSelect">{{ trans('shop::messages.fields.required_roles') }}</label>
        <select class="form-select @error('required_roles') is-invalid @enderror" id="requiredRoleSelect" name="required_roles[]" multiple>
            @foreach($roles as $role)
                <option value="{{ $role->id }}" @selected(isset($package) && $package->required_roles?->contains($role->id))>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>

        @error('required_roles')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="requiredPackagesSelect">{{ trans('shop::messages.fields.required_packages') }}</label>

        <select class="form-select @error('required_packages') is-invalid @enderror" id="requiredPackagesSelect" name="required_packages[]" multiple>
            @foreach($categories as $category)
                <optgroup label="{{ $category->name }}">
                    @foreach($category->packages as $categoryPackage)
                        @if(! isset($package) || ! $categoryPackage->is($package))
                            <option value="{{ $categoryPackage->id }}" @selected(isset($package) && $package->required_packages?->contains($categoryPackage->id))>
                                {{ $categoryPackage->name }}
                            </option>
                        @endif
                    @endforeach
                </optgroup>
            @endforeach
        </select>

        @error('required_packages')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="imageInput">{{ trans('messages.fields.image') }}</label>
        <input type="file" class="form-control @error('image') is-invalid @enderror" id="imageInput" name="image" accept=".jpg,.jpeg,.jpe,.png,.gif,.bmp,.svg,.webp" data-image-preview="filePreview">

        @error('image')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror

        <img src="{{ ($package->image ?? false) ? $package->imageUrl() : '#' }}" class="mt-2 img-fluid rounded img-preview {{ ($package->image ?? false) ? '' : 'd-none' }}" alt="Image" id="imagePreview">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">{{ trans('shop::messages.fields.commands') }}</label>

    @include('shop::admin.elements.commands', ['commands' => $package->commands ?? []])
</div>

<div class="mb-3">
    <label class="form-label" for="serversSelect">{{ trans('shop::messages.fields.servers') }}</label>

    <select class="form-select @error('servers') is-invalid @enderror" id="serversSelect" name="servers[]" multiple>
        @foreach($servers as $server)
            <option value="{{ $server->id }}" @selected(isset($package) && $package->servers->contains($server) ?? false)>{{ $server->name }}</option>
        @endforeach
    </select>

    @error('servers')
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="needOnlineSwitch" name="need_online" @checked($package->need_online ?? true)>
    <label class="form-check-label" for="needOnlineSwitch">{{ trans('shop::admin.packages.require_online') }}</label>
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="quantitySwitch" name="has_quantity" @checked($package->has_quantity ?? true)>
    <label class="form-check-label" for="quantitySwitch">{{ trans('shop::admin.packages.enable_quantity') }}</label>
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="enableSwitch" name="is_enabled" @checked($package->is_enabled ?? true)>
    <label class="form-check-label" for="enableSwitch">{{ trans('shop::admin.packages.enable') }}</label>
</div>
