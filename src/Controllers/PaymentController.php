<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Gateway;
use Azuriom\Plugin\Shop\Payment\PaymentManager;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function payment(Request $request)
    {
        if ($request->user() !== null && $request->user()->email === null && $request->filled('email')) {
            $this->validate($request, ['email' => 'required|email|max:50|unique:users']);

            $request->user()->update(['email' => $request->input('email')]);
        }

        $cart = Cart::fromSession($request->session());

        // Считаем сколько идёт через шлюз (total минус pending баланс)
        $pendingSiteMoney = (float) $request->session()->get('shop.pending_site_money', 0.0);
        $gatewayTotal = $pendingSiteMoney > 0
            ? max($cart->payableTotal() - $pendingSiteMoney, 0.0)
            : $cart->payableAfterBalance();

        // Если корзина не пуста, но платить через шлюз нечего — завершаем бесплатно
        if (! $cart->isEmpty() && $gatewayTotal < 0.1) {
            if ($pendingSiteMoney > 0 && $request->user()) {
                $request->user()->removeMoney(min($pendingSiteMoney, $cart->payableTotal()));
                $request->session()->forget('shop.pending_site_money');
            }

            PaymentManager::createPayment($cart, 0, currency(), 'free')->deliver();
            $cart->destroy();

            return to_route('shop.home')->with('success', trans('shop::messages.cart.success'));
        }

        $gateways = Gateway::enabled()
            ->get()
            ->filter(fn (Gateway $gateway) => $gateway->isSupported())
            ->reject(fn (Gateway $gateway) => $gateway->paymentMethod()->hasFixedAmount());

        // Если один шлюз — сразу перенаправляем
        if ($gateways->count() === 1) {
            return $gateways->first()->paymentMethod()->startPayment($cart, $gatewayTotal, currency());
        }

        return view('shop::payments.pay', [
            'gateways'      => $gateways,
            'gatewayTotal'  => $gatewayTotal,
            'siteMoneyUsed' => $pendingSiteMoney > 0 ? $pendingSiteMoney : $cart->getSiteMoneyAmount(),
        ]);
    }

    /**
     * Start a new payment via specific gateway.
     */
    public function pay(Request $request, Gateway $gateway)
    {
        abort_if(! $gateway->is_enabled, 403);

        $cart = Cart::fromSession($request->session());

        if ($cart->isEmpty()) {
            return to_route('shop.cart.index');
        }

        $pendingSiteMoney = (float) $request->session()->get('shop.pending_site_money', 0.0);
        $gatewayTotal = $pendingSiteMoney > 0
            ? max($cart->payableTotal() - $pendingSiteMoney, 0.0)
            : $cart->payableAfterBalance();

        return $gateway->paymentMethod()->startPayment($cart, $gatewayTotal, currency());
    }

    public function success(Request $request, Gateway $gateway)
    {
        // Списываем баланс только при успешной оплате через шлюз
        $pendingSiteMoney = (float) $request->session()->get('shop.pending_site_money', 0.0);

        if ($pendingSiteMoney > 0 && $request->user()) {
            $cart = Cart::fromSession($request->session());
            $request->user()->removeMoney(min($pendingSiteMoney, $cart->payableTotal()));
            $request->session()->forget('shop.pending_site_money');
        }

        $response = $gateway->paymentMethod()->success($request);

        Cart::fromSession($request->session())->destroy();

        return $response;
    }

    public function failure(Request $request, Gateway $gateway)
    {
        // При ошибке баланс не трогаем — pending_site_money остаётся в сессии,
        // пользователь может попробовать ещё раз.
        return $gateway->paymentMethod()->failure($request);
    }
}
