<!-- Форма авторизации и регистрации в корзине -->
<div class="card p-3 mb-4">
    @if(oauth_login())
        <div class="text-center">
            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                <i class="bi bi-box-arrow-in-right me-1"></i> Войти
            </a>
        </div>
    @else
        <div class="row">
            <div class="col-md-6 mb-3">
                <h5>Вход</h5>
                <form method="POST" action="{{ route('login') }}" id="cart-login">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="login-email">Email</label>
                        <input id="login-email" type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                        @error('email')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="login-password">Пароль</label>
                        <input id="login-password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                        @error('password')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="login-remember" @checked(old('remember'))>
                        <label class="form-check-label" for="login-remember">Запомнить меня</label>
                    </div>
                    @includeWhen($captchaLogin, 'elements.captcha', ['center' => true])
                    <button type="submit" class="btn btn-primary w-100">Войти</button>
                </form>
            </div>
            <div class="col-md-6 mb-3">
                <h5>Регистрация</h5>
                <form method="POST" action="{{ route('shop.cart.register') }}" id="cart-register">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="register-name">Имя</label>
                        <input id="register-name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name">
                        @error('name')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="register-email">Email</label>
                        <input id="register-email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                        @error('email')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="register-password">Пароль</label>
                        <input id="register-password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                        @error('password')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="register-password-confirm">Подтверждение пароля</label>
                        <input id="register-password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                    </div>
                    @includeWhen($captchaRegister, 'elements.captcha', ['center' => true])
                    <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                </form>
            </div>
        </div>
    @endif
</div>
