<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Azuriom;
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
    // https://docs.stripe.com/currencies#zero-decimal
    protected const ZERO_DECIMAL_CURRENCIES = [
        'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    // https://docs.stripe.com/currencies#three-decimal
    protected const THREE_DECIMAL_CURRENCIES = [
        'BHD', 'JOD', 'KWD', 'OMR', 'TND',
    ];

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
                'unit_amount' => $this->convertAmount($data['unit_price'], $currency),
                'product_data' => [
                    'name' => $data['item']->name(),
                    'description' => $data['item']->buyable()->getDescription(),
                ],
            ],
            'quantity' => $data['item']->quantity,
        ]);

        $payment = $this->createPayment($cart, $amount, $currency);

        $successUrl = route('shop.payments.success', [$this->id, '%id%']);

        $session = Session::create([
            'mode' => 'payment',
            'customer_email' => $payment->user->email,
            'line_items' => $items->all(),
            'success_url' => str_replace('%id%', '{CHECKOUT_SESSION_ID}', $successUrl),
            'cancel_url' => route('shop.cart.index'),
            'client_reference_id' => $payment->id,
        ]);

        $payment->update(['transaction_id' => $session->payment_intent]);

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

    public function view(): string
    {
        return 'shop::admin.gateways.methods.stripe';
    }

    public function rules(): array
    {
        return [
            'secret-key' => ['required', 'string'],
            'public-key' => ['required', 'string'],
            'endpoint-secret' => ['nullable', 'string'],
        ];
    }

    /*
     * Adapt the currency to Stripe format. See https://stripe.com/docs/currencies
     */
    protected function convertAmount(float $amount, string $currency): int
    {
        $currency = strtoupper($currency);

        if (in_array($currency, self::ZERO_DECIMAL_CURRENCIES, true)) {
            return $amount;
        }

        if (in_array($currency, self::THREE_DECIMAL_CURRENCIES, true)) {
            return $amount * 1000;
        }

        return $amount * 100;
    }

    /*
     * Retrieve decimal amount from Stripe format. See https://stripe.com/docs/currencies
     */
    protected function retrieveDecimalAmount(int $amount, string $currency): float
    {
        $currency = strtoupper($currency);

        if (in_array($currency, self::ZERO_DECIMAL_CURRENCIES, true)) {
            return $amount;
        }

        if (in_array($currency, self::THREE_DECIMAL_CURRENCIES, true)) {
            return $amount / 1000;
        }

        return $amount / 100;
    }

    protected function setup(): void
    {
        Stripe::setAppInfo('Azuriom', Azuriom::version(), 'https://azuriom.com');
        Stripe::setLogger(logger()->driver());
        Stripe::setApiKey($this->gateway->data['secret-key']);
    }
}
