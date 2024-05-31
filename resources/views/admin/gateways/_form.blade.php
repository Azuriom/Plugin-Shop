@csrf

<input type="hidden" name="type" value="{{ $type->id() }}">

<div class="mb-3">
    <label class="form-label" for="nameInput">{{ trans('messages.fields.name') }}</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="nameInput" name="name" value="{{ old('name', $gateway->name ?? $type->name()) }}">

    @error('name')
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

@include($type->view())

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="enableSwitch" name="is_enabled" @checked($gateway->is_enabled ?? true)>
    <label class="form-check-label" for="enableSwitch">{{ trans('shop::admin.gateways.enable') }}</label>
</div>

