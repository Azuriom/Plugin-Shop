<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\User;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        if (use_site_money() || ! setting('shop.guest_purchases', false)) {
            return redirect()->route('login');
        }

        $validated = $this->validate($request, [
            'name' => 'required|string|exists:users,name',
        ]);

        $user = User::firstWhere($validated);

        $request->session()->remove('shop.cart');
        $request->session()->put('shop.user', $user->id);
        $intended = $request->session()->pull('shop.url.intended');

        return $intended ? redirect()->to($intended) : redirect()->back();
    }

    public function logout(Request $request)
    {
        $request->session()->remove('shop.user');

        return redirect()->back();
    }
}
