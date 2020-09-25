<?php

namespace Azuriom\Plugin\Shop\Payment;

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Gateway;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\Method\MollieMethod;
use Azuriom\Plugin\Shop\Payment\Method\PayGolMethod;
use Azuriom\Plugin\Shop\Payment\Method\PaymentWallMethod;
use Azuriom\Plugin\Shop\Payment\Method\PayPalExpressCheckout;
use Azuriom\Plugin\Shop\Payment\Method\PayPalMethod;
use Azuriom\Plugin\Shop\Payment\Method\PaysafecardMethod;
use Azuriom\Plugin\Shop\Payment\Method\StripeMethod;
use Azuriom\Plugin\Shop\Payment\Method\XsollaMethod;

class PaymentManager
{
    /**
     * The loaded payment methods.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $paymentMethods;

    /**
     * Construct a new payment manager instance.
     */
    public function __construct()
    {
        $this->paymentMethods = collect([
            'paypal' => PayPalMethod::class,
            'paypal-express-checkout' => PayPalExpressCheckout::class,
            'mollie' => MollieMethod::class,
            'paysafecard' => PaysafecardMethod::class,
            'paygol' => PayGolMethod::class,
            'stripe' => StripeMethod::class,
            'paymentwall' => PaymentWallMethod::class,
            'xsolla' => XsollaMethod::class,
        ]);
    }

    /**
     * Get the payment methods.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPaymentMethods()
    {
        return $this->paymentMethods;
    }

    /**
     * Get a payment method.
     *
     * @param  string  $type
     * @param  \Azuriom\Plugin\Shop\Models\Gateway|null  $gateway
     * @return \Azuriom\Plugin\Shop\Payment\PaymentMethod|null
     */
    public function getPaymentMethod(string $type, Gateway $gateway = null)
    {
        return app($this->paymentMethods->get($type), $gateway ? ['gateway' => $gateway] : []);
    }

    public function getPaymentMethodOrFail(string $type, Gateway $gateway = null)
    {
        abort_if(! $this->paymentMethods->has($type), 404);

        return $this->getPaymentMethod($type, $gateway);
    }

    public function hasPaymentMethod(string $type)
    {
        return $this->paymentMethods->has($type);
    }

    public function registerPaymentMethod(string $id, $method)
    {
        $this->paymentMethods->put($id, $method);
    }

    public function buyPackages(Cart $cart)
    {
        $payment = Payment::create([
            'price' => $cart->total(),
            'gateway_type' => 'azuriom',
            'status' => 'completed',
            'currency' => 'XXX',
        ]);

        foreach ($cart->content() as $cartItem) {
            $payment->items()
                ->make([
                    'name' => $cartItem->name(),
                    'price' => $cartItem->price(),
                    'quantity' => $cartItem->quantity,
                ])
                ->buyable()->associate($cartItem->buyable())
                ->save();
        }

        $payment->coupons()->sync($cart->coupons());

        $payment->deliver();
    }
}
