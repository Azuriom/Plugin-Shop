<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Gateway;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function payment(Request $request)
    {
        $cart = Cart::fromSession($request->session());

        // If the cart isn't empty and the total is 0, just complete
        // the payment now as gateways won't accept null payment
        if (! $cart->isEmpty() && $cart->total() === 0.0) {
            payment_manager()->buyPackages($cart);

            $cart->destroy();

            return redirect()->route('shop.home')->with('success', trans('shop::messages.cart.purchase'));
        }

        $gateways = Gateway::enabled()
            ->get()
            ->filter(function ($gateway) {
                if (! payment_manager()->hasPaymentMethod($gateway->type)) {
                    return false;
                }

                return ! $gateway->paymentMethod()->hasFixedAmount();
            });

        return view('shop::payments.pay', ['gateways' => $gateways]);
    }

    /**
     * Make a new payment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Azuriom\Plugin\Shop\Models\Gateway  $gateway
     * @return \Illuminate\Http\Response
     */
    public function pay(Request $request, Gateway $gateway)
    {
        $cart = Cart::fromSession($request->session());

        if ($cart->isEmpty()) {
            return redirect()->route('shop.cart.index');
        }

        return $gateway->paymentMethod()->startPayment($cart, $cart->total(), currency());
    }

    public function success(Request $request, Gateway $gateway)
    {
        $response = $gateway->paymentMethod()->success($request);

        $cart = Cart::fromSession($request->session());

        $cart->destroy();

        return $response;
    }

    public function failure(Request $request, Gateway $gateway)
    {
        return $gateway->paymentMethod()->failure($request);
    }
}
