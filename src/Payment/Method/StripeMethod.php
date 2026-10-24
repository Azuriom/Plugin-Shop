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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Stripe\Charge;
use Stripe\Checkout\Session;
use Stripe\Coupon;
use Stripe\Dispute;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Invoice;
use Stripe\InvoicePayment;
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

        $session = Session::create(Arr::whereNotNull([
            'mode' => 'payment',
            'customer_email' => $this->getUserEmail($payment->user),
            'line_items' => $items->all(),
            'success_url' => str_replace('%id%', '{CHECKOUT_SESSION_ID}', $successUrl),
            'cancel_url' => route('shop.cart.index'),
            'client_reference_id' => $payment->id,
            'discounts' => $coupon ? [['coupon' => $coupon->id]] : [],
            'invoice_creation' => [
                'enabled' => (bool) ($this->gateway->data['invoice'] ?? false),
            ],
        ]));

        return redirect()->away($session->url);
    }

    public function startSubscription(User $user, Package $package)
    {
        $this->setup();

        $successUrl = route('shop.payments.success', [$this->id, '%id%']);

        $session = Session::create(Arr::whereNotNull([
            'mode' => 'subscription',
            'customer_email' => $this->getUserEmail($user),
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
        ]));

        return redirect()->away($session->url);
    }

    public function cancelSubscription(Subscription $subscription): void
    {
        $this->setup();

        // Stop future renewals, but preserve access for the already-paid period
        StripeSubscription::update($subscription->subscription_id, [
            'cancel_at_period_end' => true,
        ]);
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

        if (in_array($event->type, ['charge.refunded', 'charge.dispute.created'], true)) {
            return $this->processRefundOrDispute($event->type, $event->data->object);
        }

        if ($event->type === 'checkout.session.completed' || $event->type === 'checkout.session.async_payment_succeeded') {
            return $this->processCompletedCheckout($event->data->object);
        }

        if ($event->type === 'invoice.paid') {
            /** @var Invoice $invoice */
            $invoice = $event->data->object;
            $subscriptionId = $invoice->parent?->subscription_details?->subscription ?? $invoice->subscription ?? null;

            // Regular one-time invoices are not subscription renewals
            if ($subscriptionId === null) {
                return response()->noContent();
            }

            $subscription = Subscription::where('subscription_id', $subscriptionId)->first();

            // Either a subscription not created by the shop, or the first invoice of a new
            // subscription, which is handled with the checkout.session.completed event.
            if ($subscription === null) {
                Log::info("Unknown Stripe subscription {$subscriptionId} for invoice {$invoice->id}.");

                return response()->noContent();
            }

            // Zero-value invoices (e.g. paid with the customer balance) have no payment intent
            $transactionId = $invoice->amount_paid === 0
                ? $invoice->id
                : $this->paymentIntentForInvoice($invoice);

            if ($transactionId === null) {
                Log::warning("Stripe invoice {$invoice->id} has no payment intent attached.");

                return response()->json(['status' => 'no_payment_intent'], 503);
            }

            $this->renewSubscription($subscription, $transactionId, ! $subscription->payments()->exists());

            return response()->noContent();
        }

        if ($event->type === 'customer.subscription.deleted') {
            /** @var StripeSubscription $stripeSub */
            $stripeSub = $event->data->object;

            $subscription = Subscription::where('subscription_id', $stripeSub->id)->first();

            if ($subscription === null) {
                Log::warning('Unknown Stripe subscription: '.$stripeSub->id);

                return response()->noContent();
            }

            // Already expired, e.g. by the scheduler while Stripe was retrying a failed renewal
            if ($subscription->status === 'expired') {
                return response()->noContent();
            }

            // The paid-through date, not the provider's cancellation event, controls access
            if ($subscription->ends_at?->isFuture()) {
                $subscription->update(['status' => 'canceled']);
            } elseif ($subscription->payments()->exists()) {
                $subscription->expire();
            } else {
                $subscription->update(['status' => 'canceled']);
            }

            return response()->noContent();
        }

        return response()->json(['status' => 'unknown_event']);
    }

    protected function processCompletedCheckout(Session $session)
    {
        if ($session->mode === 'subscription') {
            // Completed checkout does not necessarily mean delayed payment has settled
            if (! in_array($session->payment_status, ['paid', 'no_payment_required'], true)) {
                return response()->json(['status' => 'payment_pending']);
            }

            // Zero-value invoices have no payment intent, same as in the invoice.paid event
            $transactionId = $session->payment_status === 'no_payment_required'
                ? $session->invoice
                : $this->paymentIntentForInvoice($session->invoice);

            if ($transactionId === null) {
                Log::warning('Stripe checkout invoice has no paid payment intent: '.$session->invoice);

                return response()->json(['status' => 'no_payment_intent'], 503);
            }

            // Avoid duplicated subscriptions due to Stripe re-delivering events on error
            $sub = Subscription::firstWhere('subscription_id', $session->subscription);

            if ($sub === null) {
                $user = User::find($session->metadata['user']);
                $package = Package::find($session->metadata['package']);
                $currency = strtoupper($session->currency);
                $total = $this->retrieveDecimalAmount($session->amount_total, $currency);

                $sub = $this->createSubscription($user, $package, $session->subscription, $total, $currency);
            }

            return $this->renewSubscription($sub, $transactionId, ! $sub->payments()->exists());
        }

        // Delayed payment methods complete the session before the payment succeeds
        if ($session->payment_status !== 'paid') {
            return response()->json(['status' => 'payment_pending']);
        }

        $payment = Payment::find($session->client_reference_id);

        if ($payment?->isPending()) {
            $payment->update(['transaction_id' => $session->payment_intent]);
        }

        return $this->processPayment($payment);
    }

    /**
     * Handle a refund (with a Charge object) or a dispute (with a Dispute object).
     */
    protected function processRefundOrDispute(string $type, Charge|Dispute $object)
    {
        $paymentIntentId = $object->payment_intent ?? null;
        $payment = is_string($paymentIntentId) && $paymentIntentId !== ''
            ? Payment::where('gateway_type', $this->id)->firstWhere('transaction_id', $paymentIntentId)
            : null;

        if ($payment === null) {
            Log::warning("Unknown Stripe payment for {$type}: ".($paymentIntentId ?? 'null'));

            return response()->noContent();
        }

        if ($type === 'charge.refunded') {
            // Partial refunds should not revoke the whole purchase
            return $object->refunded ? $this->processRefund($payment) : response()->noContent();
        }

        // Any dispute (inquiries included) revokes the purchase, its outcome is left to the admin
        return $this->processChargeback($payment);
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

    /**
     * Get the payment intent ID associated with the given Stripe invoice, if any.
     */
    protected function paymentIntentForInvoice(Invoice|string $invoice): ?string
    {
        $invoicePayments = InvoicePayment::all([
            'invoice' => $invoice instanceof Invoice ? $invoice->id : $invoice,
            'status' => 'paid',
            'payment' => ['type' => 'payment_intent'],
            'limit' => 1,
        ]);

        $invoicePayment = $invoicePayments->data[0] ?? null;

        return $invoicePayment?->payment?->payment_intent;
    }

    /*
     * Adapt the currency to Stripe format. See https://stripe.com/docs/currencies
     */
    protected function convertAmount(float $amount, string $currency): int
    {
        $currency = strtoupper($currency);

        if (in_array($currency, self::ZERO_DECIMAL_CURRENCIES, true)) {
            return (int) round($amount);
        }

        if (in_array($currency, self::THREE_DECIMAL_CURRENCIES, true)) {
            return (int) round($amount * 1000);
        }

        return (int) round($amount * 100);
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
            'invoice' => ['nullable', 'boolean'],
        ];
    }

    protected function setup(): void
    {
        Stripe::setAppInfo('Azuriom', Azuriom::version(), 'https://azuriom.com');
        Stripe::setLogger(logger()->driver());
        Stripe::setApiKey($this->gateway->data['secret-key']);
    }
}
