<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Package;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display the user cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('shop::cart.index', [
            'cart' => Cart::fromSession($request->session()),
        ]);
    }

    /**
     * Remove a package from the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Azuriom\Plugin\Shop\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function remove(Request $request, Package $package)
    {
        $cart = Cart::fromSession($request->session());

        $cart->remove($package);

        return redirect()->route('shop.cart.index');
    }

    /**
     * Update the quantity of the items in the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $cart = Cart::fromSession($request->session());

        foreach ($request->input('quantities', []) as $id => $quantity) {
            $item = $cart->getById($id);

            if ($item !== null && $quantity > 0) {
                $item->setQuantity($quantity);
            }

            $cart->save();
        }

        return redirect()->route('shop.cart.index');
    }

    /**
     * Clear the user cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function clear(Request $request)
    {
        Cart::fromSession($request->session())->clear();

        return redirect()->route('shop.cart.index');
    }

    /**
     * Pay using the website money.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function payment(Request $request)
    {
        if (! use_site_money()) {
            return redirect()->route('shop.cart.index');
        }

        $cart = Cart::fromSession($request->session());

        if ($cart->isEmpty()) {
            return redirect()->route('shop.cart.index');
        }

        $user = $request->user();

        if (! $user->hasMoney($cart->total())) {
            return redirect()->route('shop.cart.index')->with('error', trans('shop::messages.cart.error-money'));
        }

        $user->removeMoney($cart->total());
        $user->save();

        payment_manager()->buyPackages($cart);

        $cart->destroy();

        return redirect()->route('shop.home')->with('success', trans('shop::messages.cart.purchase'));
    }
}
