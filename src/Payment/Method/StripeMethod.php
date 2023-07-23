<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeMethod extends PaymentMethod
{
    /**
     * The payment method id name.
     *
     * @var string
     */
    protected $id = 'stripe';

    /**
     * The payment method display name.
     *
     * @var string
     */
    protected $name = 'Stripe';

    public function startPayment(Cart $cart, float $amount, string $currency)
    {
        $this->setup();

        $items = $cart->itemsPrice()->map(fn (array $data) => [
            'price_data' => [
                'currency' => $currency,
                'unit_amount' => (int) ($data['unit_price'] * 100),
                'product_data' => [
                    'name' => $data['item']->name(),
                ],
            ],
            'quantity' => $data['item']->quantity,
        ]);

        $payment = $this->createPayment($cart, $amount, $currency);

        $successUrl = route('shop.payments.success', [$this->id, '%id%']);
        $params = [
            'mode' => 'payment',
            'customer_email' => $payment->user->email,
            'line_items' => $items->all(),
            'success_url' => str_replace('%id%', '{CHECKOUT_SESSION_ID}', $successUrl),
            'cancel_url' => route('shop.cart.index'),
            'client_reference_id' => $payment->id,
        ];

        $session = Session::create($params);

        $payment->update(['transaction_id' => $session->id]);

        return redirect()->away($session->url);
    }

    public function notification(Request $request, ?string $paymentId)
    {
        $endpointSecret = $this->gateway->data['endpoint-secret'];
        $stripeSignature = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($request->getContent(), $stripeSignature, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            return response()->json([
                'error' => 'Invalid signature: '.$e->getMessage(),
            ], 400);
        }

        if ($event->type !== 'checkout.session.completed') {
            return response()->json(['status' => 'unknown']);
        }

        $payment = Payment::find($event->data->object->client_reference_id);

        return $this->processPayment($payment, $paymentId);
    }

    public function view()
    {
        return 'shop::admin.gateways.methods.stripe';
    }

    public function rules()
    {
        return [
            'secret-key' => ['required', 'string'],
            'public-key' => ['required', 'string'],
            'endpoint-secret' => ['nullable', 'string'],
        ];
    }

    private function setup()
    {
        Stripe::setLogger(logger()->driver());
        Stripe::setApiKey($this->gateway->data['secret-key']);
    }
}
