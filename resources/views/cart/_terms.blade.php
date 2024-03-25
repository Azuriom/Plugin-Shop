@if($termsUrl !== null)
    <div class="form-check d-inline-block">
        <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" name="terms" id="terms" required @checked(old('terms'))>

        <label class="form-check-label" for="terms">
            @lang('shop::messages.cart.terms', [
                'terms' => '<a href="'.$termsUrl.'" target="_blank" rel="noopener norefferer">'.trans('shop::messages.cart.terms_link').'</a>',
            ])
        </label>

        @error('terms')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
@endif
