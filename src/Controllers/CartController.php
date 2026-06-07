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

        $cart = Cart::fromSession($request->session());

        $userBalance = (auth()->check() && use_site_money()) ? auth()->user()->money : 0;

        return view('shop::cart.index', [
            'cart'          => $cart,
            'terms'         => $terms,
            'emailRequired' => auth()->check() && auth()->user()->email === null,
            'userBalance'   => $userBalance,
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
     * AJAX — сохранить, сколько донат-валюты игрок хочет потратить.
     * Вызывается ползунком на странице корзины.
     */
    public function setSiteMoney(Request $request)
    {
        if (! use_site_money()) {
            return response()->json(['error' => 'Site money disabled'], 403);
        }

        $request->validate(['amount' => 'required|numeric|min:0']);

        $cart = Cart::fromSession($request->session());

        if ($cart->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 422);
        }

        $toSpend = min((float) $request->input('amount'), $request->user()->money, $cart->payableTotal());

        $cart->setSiteMoneyAmount($toSpend);

        return response()->json([
            'site_money_amount' => $toSpend,
            'remaining_to_pay'  => round(max($cart->payableTotal() - $toSpend, 0), 2),
            'cart_total'        => round($cart->payableTotal(), 2),
        ]);
    }

    /**
     * Pay using the website money (full or partial).
     *
     * - Полная оплата балансом  → списываем и завершаем сразу.
     * - Частичная               → сохраняем в сессии, ведём на шлюз.
     * - use_site_money выключен → весь платёж через шлюз.
     */
    public function payment(Request $request)
    {
        $cart = Cart::fromSession($request->session());

        if ($cart->isEmpty()) {
            return to_route('shop.cart.index');
        }

        // use_site_money выключен — стандартный путь через шлюз
        if (! use_site_money()) {
            return to_route('shop.payments.payment');
        }

        $user           = $request->user();
        $total          = $cart->payableTotal();
        $siteMoneyToUse = $cart->getSiteMoneyAmount();

        // Проверяем реальный баланс
        if ($siteMoneyToUse > 0 && ! $user->hasMoney($siteMoneyToUse)) {
            return to_route('shop.cart.index')
                ->with('error', trans('shop::messages.cart.errors.money'));
        }

        // Полная оплата балансом
        if ($siteMoneyToUse >= $total) {
            $user->removeMoney($total);

            try {
                payment_manager()->buyPackages($cart);
            } catch (Exception $e) {
                report($e);
                $user->addMoney($total);

                return to_route('shop.cart.index')
                    ->with('error', trans('shop::messages.cart.errors.execute'));
            }

            $cart->destroy();

            return to_route('shop.home')
                ->with('success', trans('shop::messages.cart.success'));
        }

        // Частичная — отправляем на шлюз, pending запомним в сессии
        if ($siteMoneyToUse > 0) {
            $request->session()->put('shop.pending_site_money', $siteMoneyToUse);
        }

        return to_route('shop.payments.payment');
    }
}
