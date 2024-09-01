<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Azuriom;
use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Package;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Models\Subscription;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Coupon;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Invoice;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;
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
        $coupon = $this->applyGiftcards($payment, $currency);
        $successUrl = route('shop.payments.success', [$this->id, '%id%']);

        $session = Session::create([
            'mode' => 'payment',
            'customer_email' => $payment->user->email,
            'line_items' => $items->all(),
            'success_url' => str_replace('%id%', '{CHECKOUT_SESSION_ID}', $successUrl),
            'cancel_url' => route('shop.cart.index'),
            'client_reference_id' => $payment->id,
            'discounts' => $coupon ? [['coupon' => $coupon->id]] : [],
        ]);

        $payment->update(['transaction_id' => $session->payment_intent]);

        return redirect()->away($session->url);
    }

    public function startSubscription(User $user, Package $package)
    {
        $this->setup();

        $successUrl = route('shop.payments.success', [$this->id, '%id%']);

        $session = Session::create([
            'mode' => 'subscription',
            'customer_email' => $user->email,
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => currency(),
                        'unit_amount' => $this->convertAmount($package->price, currency()),
                        'product_data' => [
                            'name' => $package->name,
                            'description' => $package->short_description,
                        ],
                        'recurring' => [
                            // Convert durations to singular: e.g. months -> month
                            'interval' => rtrim($package->subscriptionPeriodUnit(), 's'),
                            'interval_count' => $package->subscriptionPeriodCount(),
                        ],
                    ],
                    'quantity' => 1,
                ],
            ],
            'success_url' => str_replace('%id%', '{CHECKOUT_SESSION_ID}', $successUrl),
            'cancel_url' => route('shop.categories.show', $package->category),
            'metadata' => ['user' => $user->id, 'package' => $package->id],
        ]);

        return redirect()->away($session->url);
    }

    public function cancelSubscription(Subscription $subscription): void
    {
        $this->setup();

        $stripeSub = StripeSubscription::retrieve($subscription->subscription_id);

        $stripeSub->cancel();
    }

    public function notification(Request $request, ?string $paymentId)
    {
        $this->setup();

        $endpointSecret = $this->gateway->data['endpoint-secret'];
        $stripeSignature = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($request->getContent(), $stripeSignature, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            return response()->json([
                'error' => 'Invalid signature: '.$e->getMessage(),
            ], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            return $this->processCompletedCheckout($event);
        }

        if ($event->type === 'invoice.paid') {
            /** @var Invoice $invoice */
            $invoice = $event->data->object;
            $subscriptionId = $invoice->subscription;

            if ($subscriptionId === null) {
                return response()->json(['status' => 'no_subscription'], 400);
            }

            $subscription = Subscription::where('subscription_id', $subscriptionId)->firstOrFail();

            if ($invoice->payment_intent !== null) {
                $this->renewSubscription($subscription, $invoice->payment_intent);
            }

            return response()->noContent();
        }

        if ($event->type === 'customer.subscription.deleted') {
            /** @var StripeSubscription $stripeSub */
            $stripeSub = $event->data->object;

            $subscription = Subscription::where('subscription_id', $stripeSub->id)->first();

            if ($subscription === null) {
                Log::warning('Unknown Stripe subscription: '.$stripeSub->id);

                return response()->json(['status' => 'unknown_subscription'], 400);
            }

            $subscription->expire();

            return response()->noContent();
        }

        return response()->json(['status' => 'unknown_event']);
    }

    protected function processCompletedCheckout(Event $event)
    {
        if ($event->data->object->mode === 'subscription') {
            /** @var \Stripe\Checkout\Session $session */
            $session = $event->data->object;
            $user = User::find($session->metadata['user']);
            $package = Package::find($session->metadata['package']);
            $invoice = Invoice::retrieve($session->invoice);
            $currency = $session->currency;
            $total = $this->retrieveDecimalAmount($session->amount_total, $currency);

            $sub = $this->createSubscription($user, $package, $session->subscription, $total, $currency);

            return $this->renewSubscription($sub, $invoice->payment_intent, true);
        }

        $payment = Payment::find($event->data->object->client_reference_id);

        return $this->processPayment($payment);
    }

    protected function applyGiftcards(Payment $payment, string $currency): ?Coupon
    {
        $amount = $payment->giftcards()->sum('amount');

        if ($amount <= 0) {
            return null;
        }

        return Coupon::create([
            'amount_off' => $this->convertAmount($amount, $currency),
            'currency' => $currency,
            'duration' => 'once',
            'max_redemptions' => 1,
            'name' => trans('shop::messages.payment.giftcards'),
        ]);
    }

    /*
     * Adapt the currency to Stripe format. See https://stripe.com/docs/currencies
     */
    protected function convertAmount(float $amount, string $currency): int
    {
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
        if (in_array($currency, self::ZERO_DECIMAL_CURRENCIES, true)) {
            return $amount;
        }

        if (in_array($currency, self::THREE_DECIMAL_CURRENCIES, true)) {
            return $amount / 1000;
        }

        return $amount / 100;
    }

    public function supportsSubscriptions()
    {
        return true;
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

    protected function setup(): void
    {
        Stripe::setAppInfo('Azuriom', Azuriom::version(), 'https://azuriom.com');
        Stripe::setLogger(logger()->driver());
        Stripe::setApiKey($this->gateway->data['secret-key']);
    }
}
