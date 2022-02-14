@include('admin.elements.date-picker')
@include('shop::admin.elements.select')

@csrf

<div class="mb-3">
    <label class="form-label" for="codeInput">{{ trans('shop::messages.fields.code') }}</label>
    <input type="text" class="form-control @error('code') is-invalid @enderror" id="codeInput" name="code" value="{{ old('code', $giftcard->code ?? '') }}" required>

    @error('code')
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="row g-3">
    <div class="mb-3 col-md-6">
        <label class="form-label" for="amountInput">{{ trans('messages.fields.money') }}</label>
        <div class="input-group @error('amount') has-validation @enderror">
            <input type="number" min="0" class="form-control @error('amount') is-invalid @enderror" id="amountInput" name="amount" value="{{ old('amount', $giftcard->amount ?? '') }}" required>
            <span class="input-group-text">{{ shop_active_currency() }}</span>

            @error('amount')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="globalLimitInput">{{ trans('shop::admin.giftcards.global_limit') }}</label>
        <input type="number" min="1" class="form-control @error('global_limit') is-invalid @enderror" id="globalLimitInput" name="global_limit" value="{{ old('global_limit', $giftcard->global_limit ?? '1') }}">

        @error('global_limit')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="row g-3">
    <div class="mb-3 col-md-6">
        <label class="form-label" for="startInput">{{ trans('shop::messages.fields.start_date') }}</label>
        <input type="text" class="form-control date-picker @error('start_at') is-invalid @enderror" id="startInput" name="start_at" value="{{ old('start_at', $giftcard->start_at ?? now()) }}" required>

        @error('start_at')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="expireInput">{{ trans('shop::messages.fields.expire_date') }}</label>
        <input type="text" class="form-control date-picker @error('expire_at') is-invalid @enderror" id="expireInput" name="expire_at" value="{{ old('expire_at', $giftcard->expire_at ?? now()->addWeek()) }}" required>

        @error('expire_at')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="enableSwitch" name="is_enabled" @if($giftcard->is_enabled ?? true) checked @endif>
    <label class="form-check-label" for="enableSwitch">{{ trans('shop::admin.giftcards.enable') }}</label>
</div>
