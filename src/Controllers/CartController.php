<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Package;
use Azuriom\Support\Markdown;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Display the user cart.
     */
    public function index(Request $request)
    {
        $terms = setting('shop.required_terms');

        if ($terms !== null) {
            $markdown = Markdown::parse($terms, true);

            $terms = new HtmlString(Str::between($markdown, '<p>', '</p>'));
        }

        return view('shop::cart.index', [
            'cart' => Cart::fromSession($request->session()),
            'terms' => $terms,
        ]);
    }

    /**
     * Remove a package from the cart.
     */
    public function remove(Request $request, Package $package)
    {
        $cart = Cart::fromSession($request->session());

        $cart->remove($package);

        return to_route('shop.cart.index');
    }

    /**
     * Update the quantity of the items in the cart.
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

        return to_route('shop.cart.index');
    }

    /**
     * Clear the user cart.
     */
    public function clear(Request $request)
    {
        Cart::fromSession($request->session())->clear();

        return to_route('shop.cart.index');
    }

    /**
     * Pay using the website money.
     */
    public function payment(Request $request)
    {
        if (! use_site_money()) {
            return to_route('shop.cart.index');
        }

        $cart = Cart::fromSession($request->session());

        if ($cart->isEmpty()) {
            return to_route('shop.cart.index');
        }

        $user = $request->user();
        $total = $cart->payableTotal();

        if (! $user->hasMoney($total)) {
            return to_route('shop.cart.index')->with('error', trans('shop::messages.cart.errors.money'));
        }

        $user->removeMoney($total);

        try {
            payment_manager()->buyPackages($cart);
        } catch (Exception $e) {
            report($e);

            $user->addMoney($total);

            return to_route('shop.cart.index')->with('error', trans('shop::messages.cart.errors.execute'));
        }

        $cart->destroy();

        return to_route('shop.home')->with('success', trans('shop::messages.cart.success'));
    }
}
