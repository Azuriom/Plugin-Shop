<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Gateway;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function payment()
    {
        $gateways = Gateway::enabled()
            ->get()
            ->filter(function ($gateway) {
                return payment_manager()->hasPaymentMethod($gateway->type) && ! $gateway->paymentMethod()->hasFixedAmount();
            });

        return view('shop::payments.pay', ['gateways' => $gateways]);
    }

    /**
     * Make a new payment.
     *
     * @param  Request  $request
     * @param  string  $gateway
     * @return \Illuminate\Http\Response
     */
    public function pay(Request $request, string $gateway)
    {
        $gateway = Gateway::where('type', $gateway)->firstOrFail();

        $cart = new Cart($request->session());

        if ($cart->isEmpty()) {
            return redirect()->route('shop.cart.index');
        }

        return $gateway->paymentMethod()->startPayment($cart, $cart->total(), currency());
    }

    public function success(Request $request, string $gateway)
    {
        $gateway = Gateway::where('type', $gateway)->firstOrFail();

        $response = $gateway->paymentMethod()->success($request);

        $cart = new Cart($request->session());

        $cart->clear();

        return $response;
    }
}
