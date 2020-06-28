@include('admin.elements.date-picker')
@include('shop::admin.elements.select')

@csrf

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="nameInput">{{ trans('messages.fields.name') }}</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="nameInput" name="name" value="{{ old('name', $discount->name ?? '') }}" required>

        @error('name')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="form-group col-md-6">
        <label for="discountInput">{{ trans('shop::messages.fields.discount') }}</label>
        <div class="input-group">
            <input type="number" min="0" max="100" class="form-control @error('discount') is-invalid @enderror" id="discountInput" name="discount" value="{{ old('discount', $discount->discount ?? '') }}" required>
            <div class="input-group-append">
                <span class="input-group-text">%</span>
            </div>

            @error('discount')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="startInput">{{ trans('shop::messages.fields.start_date') }}</label>
        <input type="text" class="form-control date-picker @error('start_at') is-invalid @enderror" id="startInput" name="start_at" value="{{ old('start_at', $discount->start_at ?? now()) }}" required>

        @error('start_at')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="form-group col-md-6">
        <label for="expireInput">{{ trans('shop::messages.fields.expire_date') }}</label>
        <input type="text" class="form-control date-picker @error('end_at') is-invalid @enderror" id="expireInput" name="end_at" value="{{ old('end_at', $discount->end_at ?? now()->addWeek()) }}" required>

        @error('end_at')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="form-group custom-control custom-switch">
    <input type="checkbox" class="custom-control-input" id="globalSwitch" name="is_global" data-toggle="collapse" data-target="#targetGroup" @if($discount->is_global ?? false) checked @endif>
    <label class="custom-control-label" for="globalSwitch">{{ trans('shop::admin.discounts.global') }}</label>
</div>

<div id="targetGroup" class="{{ $discount->is_global ?? false ? 'collapse' : 'show' }}">
    <div class="card card-body mb-2">
        <div class="form-group mb-0">
            <label for="packagesSelect">{{ trans('shop::messages.fields.packages') }}</label>

            <select class="custom-select @error('packages') is-invalid @enderror" id="packagesSelect" name="packages[]" multiple>
                @foreach($categories as $category)
                    <optgroup label="{{ $category->name }}">
                        @foreach($category->packages as $package)
                            <option value="{{ $package->id }}" @if(isset($discount) && $discount->packages->contains($package)) selected @endif>{{ $package->name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>

            @error('packages')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
</div>

<div class="form-group custom-control custom-switch">
    <input type="checkbox" class="custom-control-input" id="enableSwitch" name="is_enabled" @if($discount->is_enabled ?? true) checked @endif>
    <label class="custom-control-label" for="enableSwitch">{{ trans('shop::admin.discounts.enable') }}</label>
</div>
