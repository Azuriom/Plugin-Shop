<?php

namespace Azuriom\Plugin\Shop\Payment;

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Gateway;
use Azuriom\Plugin\Shop\Models\Giftcard;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\Method\MollieMethod;
use Azuriom\Plugin\Shop\Payment\Method\PaymentWallMethod;
use Azuriom\Plugin\Shop\Payment\Method\PayPalCheckoutMethod;
use Azuriom\Plugin\Shop\Payment\Method\PayPalMethod;
use Azuriom\Plugin\Shop\Payment\Method\PaysafecardMethod;
use Azuriom\Plugin\Shop\Payment\Method\SkrillMethod;
use Azuriom\Plugin\Shop\Payment\Method\StripeMethod;
use Azuriom\Plugin\Shop\Payment\Method\XsollaMethod;
use Illuminate\Support\Collection;

class PaymentManager
{
    /**
     * The loaded payment methods.
     */
    protected Collection $paymentMethods;

    /**
     * Construct a new payment manager instance.
     */
    public function __construct()
    {
        $this->paymentMethods = collect([
            'paypal' => PayPalMethod::class,
            'paypal-checkout' => PayPalCheckoutMethod::class,
            'mollie' => MollieMethod::class,
            'paysafecard' => PaysafecardMethod::class,
            'stripe' => StripeMethod::class,
            'paymentwall' => PaymentWallMethod::class,
            'xsolla' => XsollaMethod::class,
            'skrill' => SkrillMethod::class,
        ]);
    }

    /**
     * Get the payment methods.
     */
    public function getPaymentMethods(): Collection
    {
        return $this->paymentMethods;
    }

    /**
     * Get the payment method with the given type.
     */
    public function getPaymentMethod(string $type, ?Gateway $gateway = null): ?PaymentMethod
    {
        $class = $this->paymentMethods->get($type);

        return $class ? app($class, $gateway ? ['gateway' => $gateway] : []) : null;
    }

    public function getPaymentMethodOrFail(string $type, ?Gateway $gateway = null): PaymentMethod
    {
        abort_if(! $this->paymentMethods->has($type), 404);

        return $this->getPaymentMethod($type, $gateway);
    }

    public function hasPaymentMethod(string $type): bool
    {
        return $this->paymentMethods->has($type);
    }

    public function registerPaymentMethod(string $id, string $method): void
    {
        $this->paymentMethods->put($id, $method);
    }

    public function buyPackages(Cart $cart): void
    {
        $payment = Payment::create([
            'price' => $cart->payableTotal(),
            'gateway_type' => 'azuriom',
            'status' => 'completed',
            'currency' => 'XXX',
        ]);

        foreach ($cart->content() as $item) {
            $payment->items()
                ->make([
                    'name' => $item->name(),
                    'price' => $item->price(),
                    'quantity' => $item->quantity,
                    'variables' => $item->variables,
                ])
                ->buyable()
                ->associate($item->buyable())
                ->save();
        }

        $payment->coupons()->sync($cart->coupons());
        $payment->processGiftcards($cart->total(), $cart->giftcards());
        $payment->deliver();
    }

    public static function createPayment(Cart $cart, float $price, string $currency, string $gatewayId, ?string $paymentId = null): Payment
    {
        $payment = Payment::create([
            'price' => $price,
            'currency' => $currency,
            'gateway_type' => $gatewayId,
            'status' => 'pending',
            'transaction_id' => $paymentId,
        ]);

        foreach ($cart->content() as $item) {
            $payment->items()
                ->make([
                    'name' => $item->name(),
                    'price' => $item->price(),
                    'quantity' => $item->quantity,
                    'variables' => $item->variables,
                ])
                ->buyable()->associate($item->buyable())
                ->save();
        }

        $payment->coupons()->sync($cart->coupons());
        $payment->processGiftcards($cart->total(), $cart->giftcards());
        $cart->giftcards()->each(fn (Giftcard $card) => $cart->removeGiftcard($card));

        return $payment;
    }
}
