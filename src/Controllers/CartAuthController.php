<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\Role;
use Azuriom\Models\User;
use Azuriom\Rules\GameAuth;
use Azuriom\Rules\Username;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CartAuthController extends Controller
{
    /**
     * Регистрация пользователя из корзины.
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        event(new Registered($user));

        // Если подтверждение почты отключено, помечаем её подтвержденной
        if (!setting('shop.email_verification', true)) {
            $user->markEmailAsVerified();
        }

        Auth::login($user);

        return redirect()->intended(route('shop.cart.index'));
    }

    /**
     * Правила валидации при регистрации.
     */
    protected function validator(array $data)
    {
        $rules = [
            'name' => ['required', 'string', 'max:25', 'unique:users', new Username()],
            'email' => ['required', 'string', 'email', 'max:50', 'unique:users'],
            'password' => ['required', 'confirmed', Password::default()],
        ];

        // Проверяем имя пользователя через игру, если она установлена
        if (game()->id() !== 'none') {
            $rules['name'][] = new GameAuth();
        }

        return Validator::make($data, $rules);
    }

    /**
     * Создание пользователя.
     */
    protected function create(array $data): User
    {
        return User::forceCreate([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role_id' => Role::defaultRoleId(),
            'game_id' => game()->getUserUniqueId($data['name']),
            'last_login_ip' => FacadesRequest::ip(),
            'last_login_at' => now(),
        ]);
    }
}
