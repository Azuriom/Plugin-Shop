@include('admin.elements.date-picker')

@csrf

<div class="@if($row) row gx-3 @endif">
    <div class="mb-3  @if($row) col-md-6 @endif">
        <label class="form-label" for="startInput">{{ trans('shop::messages.fields.start_date') }}</label>
        <input type="text" class="form-control date-picker @error('start_at') is-invalid @enderror" id="startInput" name="start_at" value="{{ old('start_at', $giftcard->start_at ?? now()) }}" required>

        @error('start_at')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 @if($row) col-md-6 @endif">
        <label class="form-label" for="expireInput">{{ trans('shop::messages.fields.expire_date') }}</label>
        <input type="text" class="form-control date-picker @error('expire_at') is-invalid @enderror" id="expireInput" name="expire_at" value="{{ old('expire_at', $giftcard->expire_at ?? now()->addYear()) }}" required>

        @error('expire_at')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>
