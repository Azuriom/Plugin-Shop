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

<div class="row gx-3">
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
        <label class="form-label" for="categorySelect">{{ trans('shop::messages.fields.category') }}</label>
        <select class="form-select @error('category_id') is-invalid @enderror" id="categorySelect" name="category_id" required>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(isset($package) && $category->is($package->category))>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>

        @error('category_id')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

@if(scheduler_running())
    <div class="row gx-3" v-scope="{ ...parsePeriod('{{ old('billing_period', $package->billing_period ?? '') }}'), billing: '{{ old('billing_type', $package->billing_type ?? 'one-off') }}' }">
        <div class="mb-3" :class="billing !== 'one-off' ? 'col-md-6' : 'col-md-12'">
            <label class="form-label" for="billingSelect">{{ trans('shop::admin.packages.billing') }}</label>
            <select class="form-select" id="billingSelect" name="billing_type" required v-model="billing">
                <option value="one-off">{{ trans('shop::admin.packages.one_off') }}</option>
                <option value="expiring">{{ trans('shop::admin.packages.expiring') }}</option>
                <option value="subscription">{{ trans('shop::admin.packages.subscription') }}</option>
            </select>
        </div>

        <div class="mb-3 col-md-6" v-if="billing !== 'one-off'">
            <label class="form-label" for="billingValue">{{ trans('shop::admin.packages.billing_period') }}</label>

            <div class="input-group @error('billing_period') has-validation @enderror">
                <span v-if="billing === 'subscription'" class="input-group-text">
                    {{ trans('shop::admin.packages.every') }}
                </span>

                <span v-else-if="billing === 'expiring'" class="input-group-text">
                    {{ trans('shop::admin.packages.after') }}
                </span>

                <input type="number" min="0" class="form-control" id="billingValue" v-model="value" required>

                <select class="form-select @error('billing_period') is-invalid @enderror" v-model="unit" aria-label="{{ trans('shop::admin.packages.billing_period') }}">
                    @foreach(['days', 'weeks', 'months', 'years'] as $unit)
                        <option value="{{ $unit }}">
                            {{ trans('shop::messages.periods.'.$unit) }}
                        </option>
                    @endforeach
                </select>

                @error('billing_period')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            <input type="hidden" name="billing_period" :value="value + ' ' + unit">
        </div>

        <div v-if="billing === 'subscription'" class="col-12">
            <div class="alert alert-info" role="alert">
                <i class="bi bi-info-circle"></i> @lang('shop::admin.packages.subscription_info')
            </div>
        </div>

        <div v-else-if="billing === 'expiring'" class="col-12">
            <div class="alert alert-info" role="alert">
                <i class="bi bi-info-circle"></i> @lang('shop::admin.packages.expiring_info')
            </div>
        </div>
    </div>
@else
    <input type="hidden" name="billing_type" value="one-off">

    <div class="alert alert-info" role="alert">
        <i class="bi bi-info-circle"></i> @lang('shop::admin.packages.scheduler')
    </div>
@endif

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="customPriceSwitch" name="custom_price" @checked($package->custom_price ?? false)>
    <label class="form-check-label" for="customPriceSwitch">{{ trans('shop::admin.packages.custom_price') }}</label>
</div>

<div class="row gx-3 mt-4">
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
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="userLimitSwitch" name="has_user_limit" data-bs-toggle="collapse" data-bs-target="#userLimit" @checked(old('user_limit', $package->user_limit ?? false))>
    <label class="form-check-label" for="userLimitSwitch">{{ trans('shop::admin.packages.has_user_limit') }}</label>
</div>

<div id="userLimit" class="{{ old('user_limit', $package->user_limit ?? false) ? 'show' : 'collapse' }}">
    <div class="card mb-3">
        <div class="card-body row gx-3">
            <div class="mb-3 col-md-6">
                <label class="form-label" for="userLimitInput">{{ trans('shop::messages.fields.user_limit') }}</label>
                <input type="number" class="form-control @error('user_limit') is-invalid @enderror" id="userLimitInput" name="user_limit" value="{{ old('user_limit', $package->user_limit ?? '') }}">

                @error('user_limit')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="mb-3 col-md-6" v-scope="{ ...parsePeriod('{{ old('user_limit_period', $package->user_limit_period ?? '') }}') }">
                <label class="form-label" for="userLimitInput">{{ trans('shop::admin.packages.limit_period') }}</label>

                <div class="input-group @error('user_limit_period') has-validation @enderror">
                    <span v-if="unit" class="input-group-text">
                        {{ trans('shop::admin.packages.every') }}
                    </span>

                    <input v-if="unit" type="number" min="0" class="form-control" id="userLimitInput" v-model="value">

                    <select class="form-select @error('user_limit_period') is-invalid @enderror" id="userLimitUnit" v-model="unit" aria-label="{{ trans('shop::admin.packages.limit_period') }}">
                        <option value="">{{ trans('shop::admin.packages.no_period') }}</option>
                        @foreach(['hours', 'days', 'weeks', 'months', 'years'] as $unit)
                            <option value="{{ $unit }}">
                                {{ trans('shop::messages.periods.'.$unit) }}
                            </option>
                        @endforeach
                    </select>

                    @error('user_limit_period')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <input type="hidden" name="user_limit_period" :value="unit ? value + ' ' + unit : ''">
            </div>
        </div>
    </div>
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="globalLimitSwitch" name="has_global_limit" data-bs-toggle="collapse" data-bs-target="#globalLimit" @checked(old('global_limit', $package->global_limit ?? false))>
    <label class="form-check-label" for="globalLimitSwitch">{{ trans('shop::admin.packages.has_global_limit') }}</label>
</div>

<div id="globalLimit" class="{{ old('global_limit', $package->global_limit ?? false) ? 'show' : 'collapse' }}">
    <div class="card mb-3">
        <div class="card-body row gx-3">
            <div class="mb-3 col-md-6">
                <label class="form-label" for="globalLimitInput">{{ trans('shop::messages.fields.global_limit') }}</label>
                <input type="number" class="form-control @error('global_limit') is-invalid @enderror" id="globalLimitInput" name="global_limit" value="{{ old('global_limit', $package->global_limit ?? '') }}">

                @error('global_limit')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="mb-3 col-md-6" v-scope="{ ...parsePeriod('{{ old('global_limit_period', $package->global_limit_period ?? '') }}') }">
                <label class="form-label" for="globalLimitInput">{{ trans('shop::admin.packages.limit_period') }}</label>

                <div class="input-group @error('global_limit_period') has-validation @enderror">
                    <span v-if="unit" class="input-group-text">
                        {{ trans('shop::admin.packages.every') }}
                    </span>

                    <input v-if="unit" type="number" min="0" class="form-control" id="globalLimitInput" v-model="value">

                    <select class="form-select @error('global_limit_period') is-invalid @enderror" id="globalLimitUnit" v-model="unit" aria-label="{{ trans('shop::admin.packages.limit_period') }}">
                        <option value="">{{ trans('shop::admin.packages.no_period') }}</option>
                        @foreach(['hours', 'days', 'weeks', 'months', 'years'] as $unit)
                            <option value="{{ $unit }}">
                                {{ trans('shop::messages.periods.'.$unit) }}
                            </option>
                        @endforeach
                    </select>

                    @error('global_limit_period')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <input type="hidden" name="global_limit_period" :value="unit ? value + ' ' + unit : ''">
            </div>
        </div>
    </div>
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="noExpired" name="limits_no_expired" @checked($package->limits_no_expired ?? false)>
    <label class="form-check-label" for="noExpired">{{ trans('shop::admin.packages.limits_no_expired') }}</label>
</div>

<div class="row gx-3">
    <div class="mb-3 @if(scheduler_running()) col-md-6 @else col-12 @endif">
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

    @if(scheduler_running())
        <div class="mb-3 col-md-6">
            <label class="form-label" for="expireRoleSelect">{{ trans('shop::admin.packages.role') }}</label>
            <select class="form-select @error('expired_role_id') is-invalid @enderror" id="expireRoleSelect" name="expired_role_id">
                <option value="">{{ trans('messages.none') }}</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected((int) ($package->expired_role_id ?? 0) === $role->id)>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>

            @error('expired_role_id')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    @endif
</div>

<div class="row gx-3">
    <div class="mb-3 col-md-6">
        <label class="form-label" for="variablesSelect">{{ trans('shop::admin.packages.variables') }}</label>
        <select class="form-select @error('variables') is-invalid @enderror" id="variablesSelect" name="variables[]" multiple>
            @foreach($variables as $variable)
                <option value="{{ $variable->id }}" @selected(isset($package) && $package->variables->contains($variable))>
                    {{ $variable->name }}
                </option>
            @endforeach
        </select>

        @error('variables')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="moneyInput">{{ trans('shop::admin.packages.money') }}</label>

        <div class="input-group @error('money') has-validation @enderror">
            <input type="number" min="0" step="0.01" max="999999" class="form-control @error('money') is-invalid @enderror" id="moneyInput" name="money" value="{{ old('money', $package->money ?? '') }}">
            <span class="input-group-text">{{ money_name() }}</span>

            @error('money')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
</div>

<div class="row gx-3">
    <div class="col-md-6">
        @include('shop::admin.packages._files')
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="imageInput">{{ trans('messages.fields.image') }}</label>
        <input type="file" class="form-control @error('image') is-invalid @enderror" id="imageInput" name="image" accept=".jpg,.jpeg,.jpe,.png,.gif,.bmp,.svg,.webp" data-image-preview="imagePreview">

        @error('image')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror

        <img src="{{ ($package->image ?? false) ? $package->imageUrl() : '#' }}" class="mt-2 img-fluid rounded img-preview {{ ($package->image ?? false) ? '' : 'd-none' }}" alt="Image" id="imagePreview">
    </div>
</div>

<h2 class="h4">{{ trans('shop::messages.fields.commands') }}</h2>

@if($servers->isEmpty())
    <div class="alert alert-info" role="alert">
        <p><i class="bi bi-info-circle"></i> @lang('shop::admin.commands.servers')</p>

        <a href="{{ route('admin.servers.index') }}" target="_blank" class="btn btn-primary btn-sm">
            <i class="bi bi-hdd-network"></i> {{ trans('admin.servers.title') }}
        </a>
    </div>
@else
    @include('shop::admin.commands._form', ['commands' => $package->commands ?? []])
@endif

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="giftcardSwitch" name="has_giftcard" data-bs-toggle="collapse" data-bs-target="#giftcard" @checked(old('has_giftcard', optional($package ?? null)->hasGiftcard()))>
    <label class="form-check-label" for="giftcardSwitch">{{ trans('shop::admin.packages.has_giftcard') }}</label>
</div>

<div id="giftcard" class="{{ old('has_giftcard', optional($package ?? null)->hasGiftcard()) ? 'show' : 'collapse' }}">
    <div class="card mb-3">
        <div class="card-body" v-scope="{ balance: '{{ old('giftcard_balance', $package->giftcard_balance ?? '') }}', balanceFixed: '{{ old('giftcard_fixed', ($package->giftcard_balance ?? 1) > 0) }}' }">
            <label class="form-label" for="balanceInput">{{ trans('shop::admin.packages.giftcard_balance') }}</label>

            <div class="input-group @error('giftcard_balance') has-validation @enderror">
                <input v-if="balanceFixed" type="number" step="0.01" min="0" class="form-control @error('giftcard_balance') is-invalid @enderror" id="balanceInput" name="giftcard_balance" v-model="balance">

                <select class="form-select @error('giftcard_balance') is-invalid @enderror" v-model="balanceFixed" name="giftcard_fixed">
                    <option value="1">{{ shop_active_currency() }}</option>
                    <option value="">{{ trans('shop::admin.packages.giftcard_dynamic') }}</option>
                </select>

                @error('giftcard_balance')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="quantitySwitch" name="has_quantity" @checked($package->has_quantity ?? true)>
    <label class="form-check-label" for="quantitySwitch">{{ trans('shop::admin.packages.enable_quantity') }}</label>
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="enableSwitch" name="is_enabled" @checked($package->is_enabled ?? true)>
    <label class="form-check-label" for="enableSwitch">{{ trans('shop::admin.packages.enable') }}</label>
</div>

@push('scripts')
    <script>
        function parsePeriod(value) {
            if (!value) {
                return { value: '1', unit: '' }
            }

            const parsed = value.split(' ', 2)

            return { value: parsed[0], unit: parsed[1] }
        }
    </script>
@endpush
