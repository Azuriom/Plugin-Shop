@include('admin.elements.date-picker')
@include('shop::admin.elements.select')

@push('styles')
    <style>
        .input-group-append .form-select {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
    </style>
@endpush

@csrf

<div class="row gx-3">
    <div class="mb-3 col-md-6">
        <label class="form-label" for="codeInput">{{ trans('shop::messages.fields.code') }}</label>
        <input type="text" class="form-control @error('code') is-invalid @enderror" id="codeInput" name="code" value="{{ old('code', $coupon->code ?? '') }}" required>

        @error('code')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="discountInput">{{ trans('shop::messages.fields.discount') }}</label>
        <div class="input-group">
            <input type="number" min="0" class="form-control @error('discount') is-invalid @enderror" id="discountInput" name="discount" value="{{ old('discount', $coupon->discount ?? '') }}" required>
            <div class="input-group-append">
                <select class="form-select" name="is_fixed">
                    <option value="0" @selected(!old('is_fixed', $coupon->is_fixed ?? false))>%</option>
                    <option value="1" @selected(old('is_fixed', $coupon->is_fixed ?? false)) >{{ shop_active_currency() }}</option>
                </select>
            </div>

            @error('discount')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
</div>

<div class="row gx-3">
    <div class="mb-3 col-md-6">
        <label class="form-label" for="userLimitInput">{{ trans('shop::admin.coupons.user_limit') }}</label>
        <input type="number" min="0" class="form-control @error('user_limit') is-invalid @enderror" id="userLimitInput" name="user_limit" value="{{ old('user_limit', $coupon->user_limit ?? '') }}">

        @error('user_limit')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="globalLimitInput">{{ trans('shop::admin.coupons.global_limit') }}</label>
        <input type="number" min="0" class="form-control @error('global_limit') is-invalid @enderror" id="globalLimitInput" name="global_limit" value="{{ old('global_limit', $coupon->global_limit ?? '') }}">

        @error('global_limit')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="row gx-3">
    <div class="mb-3 col-md-6">
        <label class="form-label" for="startInput">{{ trans('shop::messages.fields.start_date') }}</label>
        <input type="text" class="form-control date-picker @error('start_at') is-invalid @enderror" id="startInput" name="start_at" value="{{ old('start_at', $coupon->start_at ?? now()) }}" required>

        @error('start_at')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="expireInput">{{ trans('shop::messages.fields.expire_date') }}</label>
        <input type="text" class="form-control date-picker @error('expire_at') is-invalid @enderror" id="expireInput" name="expire_at" value="{{ old('expire_at', $coupon->expire_at ?? now()->addMonth()) }}" required>

        @error('expire_at')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="globalSwitch" name="is_global" data-bs-toggle="collapse" data-bs-target="#targetGroup" @checked(old('is_global', $coupon->is_global ?? false))>
    <label class="form-check-label" for="globalSwitch">{{ trans('shop::admin.coupons.global') }}</label>
</div>

<div id="targetGroup" class="{{ old('is_global', $coupon->is_global ?? false) ? 'collapse' : 'show' }}">
    <div class="card card-body mb-2">
        <div class="mb-3 mb-0">
            <label class="form-label" for="packagesSelect">{{ trans('shop::messages.fields.packages') }}</label>

            <select class="form-select @error('packages') is-invalid @enderror" id="packagesSelect" name="packages[]" multiple>
                @foreach($categories as $category)
                    <optgroup label="{{ $category->name }}">
                        @foreach($category->packages as $package)
                            <option value="{{ $package->id }}" @selected(isset($coupon) && $coupon->packages->contains($package))>
                                {{ $package->name }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>

            @error('packages')
            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="cumulateSwitch" name="can_cumulate" @checked($coupon->can_cumulate ?? true)>
    <label class="form-check-label" for="cumulateSwitch">{{ trans('shop::admin.coupons.cumulate') }}</label>
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="enableSwitch" name="is_enabled" @checked($coupon->is_enabled ?? true)>
    <label class="form-check-label" for="enableSwitch">{{ trans('shop::admin.coupons.enable') }}</label>
</div>
