<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Cart\CartItem;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
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

        $items = $cart->content()->map(function (CartItem $item) use ($currency) {
            return [
                'name' => $item->name(),
                'amount' => (int) $item->buyable()->getPrice() * 100,
                'description' => $item->buyable()->getDescription(),
                'currency' => $currency,
                'quantity' => $item->quantity,
            ];
        });

        $payment = $this->createPayment($cart, $amount, $currency);

        $successUrl = route('shop.payments.success', [$this->id, '%id%']);

        $session = Session::create([
            'customer_email' => auth()->user()->email,
            'payment_method_types' => ['card'],
            'line_items' => $items->toArray(),
            'success_url' => str_replace('%id%', '{CHECKOUT_SESSION_ID}', $successUrl),
            'cancel_url' => route('shop.cart.index'),
            'client_reference_id' => $payment->id,
        ]);

        $payment->update(['transaction_id' => $payment->id]);

        return view('shop::payments.stripe', [
            'checkoutSessionId' => $session->id,
            'stripeApiKey' => $this->gateway->data['public-key'],
        ]);
    }

    public function notification(Request $request, ?string $paymentId)
    {
        $endpointSecret = $this->gateway->data['endpoint-secret'];
        $stripeSignature = $request->header('Stripe-Signature');

        $event = Webhook::constructEvent($request->getContent(), $stripeSignature, $endpointSecret);

        if ($event->type !== 'checkout.session.completed') {
            return response()->json(['status' => 'unknown']);
        }

        $payment = Payment::find($event->data->object->client_reference_id);

        return $this->processPayment($payment, $paymentId);
    }

    public function success(Request $request)
    {
        return view('shop::payments.success');
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
