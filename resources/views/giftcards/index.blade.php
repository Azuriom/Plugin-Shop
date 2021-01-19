<form action="{{ route('shop.giftcards') }}" method="POST">
    @csrf

    <div class="form-group">
        <input type="text" class="form-control @error('code') is-invalid @enderror mx-2" placeholder="Code" id="code" name="code" value="{{ old('code') }}">

        @error('code')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary">
        {{ trans('messages.actions.send') }}
    </button>
</form>