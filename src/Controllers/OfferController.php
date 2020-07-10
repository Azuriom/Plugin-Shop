<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Gateway;
use Azuriom\Plugin\Shop\Models\Offer;

class OfferController extends Controller
{
    /**
     * Construct a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_if(! use_site_money(), 403);

            return $next($request);
        });
    }

    public function selectPayment()
    {
        $gateways = Gateway::enabled()
            ->get()
            ->filter(function ($gateway) {
                return payment_manager()->hasPaymentMethod($gateway->type);
            })->load('offers');

        return view('shop::offers.payment', ['gateways' => $gateways]);
    }

    public function buy(Gateway $gateway)
    {
        $gateway->load('offers');

        if ($gateway->paymentMethod()->hasFixedAmount()) {
            return $gateway->paymentMethod()->startPayment(Cart::createEmpty(), 0, currency());
        }

        return view('shop::offers.select', [
            'gateway' => $gateway,
            'offers' => $gateway->offers,
        ]);
    }

    public function pay(Offer $offer, Gateway $gateway)
    {
        $cart = Cart::createEmpty();

        $cart->add($offer);

        return $gateway->paymentMethod()->startPayment($cart, $cart->total(), currency());
    }
}
