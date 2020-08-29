@csrf

<div class="form-group">
    <label for="nameInput">{{ trans('messages.fields.name') }}</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="nameInput" name="name" value="{{ old('name', $category->name ?? '') }}" required>

    @error('name')
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="form-group custom-control custom-switch">
    <input type="checkbox" class="custom-control-input" id="cumulatePurchasesSwitch" name="cumulate_purchases" @if(old('cumulate_purchases', $category->cumulate_purchases ?? false)) checked @endif>
    <label class="custom-control-label" for="cumulatePurchasesSwitch">{{ trans('shop::admin.categories.cumulate_purchases') }}</label>
</div>

<div class="form-group custom-control custom-switch">
    <input type="checkbox" class="custom-control-input" id="enableSwitch" name="is_enabled" @if(old('is_enabled', $category->is_enabled ?? true)) checked @endif>
    <label class="custom-control-label" for="enableSwitch">{{ trans('shop::admin.categories.enable') }}</label>
</div>
