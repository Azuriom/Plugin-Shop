<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Package;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Models\Subscription;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JsonException;

class PayPalCheckoutMethod extends PaymentMethod
{
    /**
     * The payment method id name.
     *
     * @var string
     */
    protected $id = 'paypal-checkout';

    /**
     * The payment method display name.
     *
     * @var string
     */
    protected $name = 'PayPal Checkout';

    /**
     * The payment method image.
     *
     * @var string
     */
    protected $image = 'paypal.svg';

    public function startPayment(Cart $cart, float $amount, string $currency)
    {
        $payment = $this->createPayment($cart, $amount, $currency);

        // Use the final shop total. PayPal itemization requires a matching amount
        // breakdown, which is not reliable here with gift cards and discounts.
        $response = $this->getClient()->post('/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $payment->id,
                    'description' => $this->getPurchaseDescription($payment),
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => number_format($amount, 2, '.', ''),
                    ],
                ],
            ],
            'payment_source' => [
                'card' => [
                    'experience_context' => [
                        'return_url' => route('shop.payments.success', $this->id),
                        'cancel_url' => route('shop.cart.index'),
                    ],
                ],
                'paypal' => [
                    'experience_context' => [
                        'shipping_preference' => 'NO_SHIPPING',
                        'user_action' => 'PAY_NOW',
                        'return_url' => route('shop.payments.success', $this->id),
                        'cancel_url' => route('shop.cart.index'),
                    ],
                ],
            ],
        ]);

        $id = $response->json('id');

        $payment->update(['transaction_id' => $id]);

        return view('shop::gateways.paypal-checkout', [
            'sandbox' => $this->gateway->data['environment'] === 'sandbox',
            'clientId' => $this->gateway->data['client-id'],
            'paypalId' => $id,
            'payment' => $payment,
            'description' => $this->getPurchaseDescription($payment),
            'currency' => $currency,
            'successUrl' => route('shop.payments.success', $this->id),
            'captureUrl' => route('shop.payments.notification', $this->id),
        ]);
    }

    public function startSubscription(User $user, Package $package)
    {
        $planId = $this->findOrCreatePlan($package);
        $email = $this->getUserEmail($user);

        $response = $this->getClient()->post('/v1/billing/subscriptions', Arr::whereNotNull([
            'custom_id' => $user->id.'|'.$package->id,
            'plan_id' => $planId,
            'subscriber' => $email !== null ? ['email_address' => $email] : null,
            'application_context' => [
                'shipping_preference' => 'NO_SHIPPING',
                'return_url' => route('shop.payments.success', $this->id),
                'cancel_url' => route('shop.cart.index'),
            ],
        ]));

        $links = collect($response->json('links'));

        return redirect()->away(Arr::get($links->firstWhere('rel', 'approve'), 'href'));
    }

    public function cancelSubscription(Subscription $subscription): void
    {
        $id = $subscription->subscription_id;

        $this->getClient()->post('v1/billing/subscriptions/'.$id.'/cancel', [
            'reason' => 'User canceled',
        ]);
    }

    public function notification(Request $request, ?string $paymentId)
    {
        if ($paymentId !== null) {
            return $this->capturePayPalOrder($paymentId);
        }

        $type = $this->verifyPayPalWebhook($request);

        if ($type === 'BILLING.SUBSCRIPTION.ACTIVATED') {
            $ids = $this->parseSubscriptionCustomId($request->json('resource.custom_id'));

            if ($ids === null) {
                return response()->noContent();
            }

            [$userId, $packageId] = $ids;
            $subscriptionId = $request->json('resource.id');

            // Activation is not proof of payment. Wait for PAYMENT.SALE.COMPLETED.
            Log::info('Waiting payment for PayPal subscription '.$subscriptionId.', for user '.$userId.' and package '.$packageId);

            return response()->json(['status' => 'subscription_pending']);
        }

        if ($type === 'PAYMENT.SALE.COMPLETED') {
            $subscriptionId = $request->json('resource.billing_agreement_id');
            $paymentId = $request->json('resource.id');

            if (! is_string($subscriptionId) || ! is_string($paymentId)) {
                return response()->json(['status' => 'invalid_subscription_sale'], 400);
            }

            $subscription = Subscription::firstWhere('subscription_id', $subscriptionId);

            if ($subscription !== null) {
                return $this->renewSubscription($subscription, $paymentId, ! $subscription->payments()->exists());
            }

            $response = $this->getClient()->get('/v1/billing/subscriptions/'.$subscriptionId);
            $ids = $this->parseSubscriptionCustomId($response->json('custom_id'));

            if ($ids === null) {
                Log::info('Unknown PayPal subscription: '.$subscriptionId);

                return response()->noContent();
            }

            [$userId, $packageId] = $ids;

            return $this->createPayPalSubscription($userId, $packageId, $subscriptionId, $paymentId);
        }

        if ($type === 'PAYMENT.CAPTURE.COMPLETED') {
            $orderId = $request->json('resource.supplementary_data.related_ids.order_id');

            if ($orderId !== null) {
                $this->reconcilePayPalOrder($orderId);
            }

            // Always acknowledge, as orders not created by the shop will never be processed
            return response()->noContent();
        }

        if (in_array($type, ['BILLING.SUBSCRIPTION.CANCELLED', 'BILLING.SUBSCRIPTION.EXPIRED'], true)) {
            $subscription = Subscription::firstWhere('subscription_id', $request->json('resource.id'));

            if ($subscription !== null && $subscription->status !== 'expired') {
                // Preserve remaining paid access; expiration jobs enforce ends_at.
                $subscription->update(['status' => 'canceled']);
            }

            return response()->noContent();
        }

        if (in_array($type, ['PAYMENT.SALE.REFUNDED', 'PAYMENT.SALE.REVERSED'], true)) {
            $payment = null;

            // Depending on the event, the resource ID may identify the refund
            // rather than the original sale. Prefer the original sale ID when present.
            foreach (['resource.sale_id', 'resource.id'] as $path) {
                $saleId = $request->json($path);

                if ($saleId !== null) {
                    $payment = Payment::where('gateway_type', $this->id)
                        ->firstWhere('transaction_id', $saleId);
                }

                if ($payment !== null) {
                    break;
                }
            }

            if ($payment === null) {
                Log::warning('Unable to match PayPal subscription refund/reversal to a sale.');

                return response()->noContent();
            }

            if ($type === 'PAYMENT.SALE.REVERSED') {
                return $this->processChargeback($payment);
            }

            $refunded = $request->json('resource.total_refunded_amount.value')
                ?? $request->json('resource.amount.total');

            return $this->isFullRefund($payment, $refunded)
                ? $this->processRefund($payment)
                : response()->noContent();
        }

        if ($type === 'CUSTOMER.DISPUTE.CREATED') {
            return $this->processPayPalDispute($request->json('resource') ?? []);
        }

        if ($type === 'PAYMENT.CAPTURE.REFUNDED' || $type === 'PAYMENT.CAPTURE.REVERSED') {
            $links = collect($request->json('resource.links'));
            $upUrl = Arr::get($links->firstWhere('rel', 'up'), 'href');
            $transactionId = $request->json('resource.supplementary_data.related_ids.capture_id')
                ?? ($upUrl !== null ? Str::afterLast($upUrl, '/') : null);
            $payment = $transactionId !== null
                ? Payment::where('gateway_type', $this->id)->firstWhere('transaction_id', $transactionId)
                : null;

            if ($payment === null) {
                Log::warning('Unable to match PayPal capture refund/reversal to a payment.');

                return response()->noContent();
            }

            if ($type === 'PAYMENT.CAPTURE.REVERSED') {
                return $this->processChargeback($payment);
            }

            $refunded = $request->json('resource.seller_payable_breakdown.total_refunded_amount.value')
                ?? $request->json('resource.amount.value');

            return $this->isFullRefund($payment, $refunded)
                ? $this->processRefund($payment)
                : response()->noContent();
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle a new dispute (inquiries included) by revoking the purchase.
     * The outcome of disputes is left to the admin.
     */
    protected function processPayPalDispute(array $dispute)
    {
        foreach (Arr::get($dispute, 'disputed_transactions') ?? [] as $transaction) {
            $transactionId = Arr::get($transaction, 'seller_transaction_id');
            $payment = is_string($transactionId)
                ? Payment::where('gateway_type', $this->id)->firstWhere('transaction_id', $transactionId)
                : null;

            if ($payment !== null) {
                $this->processChargeback($payment);
            }
        }

        return response()->noContent();
    }

    protected function createPayPalSubscription(string $userId, string $packageId, string $subscription, string $payment)
    {
        $existing = Subscription::firstWhere('subscription_id', $subscription);

        if ($existing !== null) {
            return $this->renewSubscription($existing, $payment, ! $existing->payments()->exists());
        }

        $user = User::findOrFail($userId);
        $package = Package::findOrFail($packageId);
        $subscription = $this->createSubscription($user, $package, $subscription);

        return $this->renewSubscription($subscription, $payment, true);
    }

    protected function capturePayPalOrder(string $orderId)
    {
        $payment = Payment::where('gateway_type', $this->id)
            ->firstWhere('transaction_id', $orderId);

        // After a capture we store the capture ID, so a duplicate client callback
        // must be reconciled against the remote order rather than captured again.
        if ($payment === null || ! $payment->isPending()) {
            return $this->reconcilePayPalOrder($orderId);
        }

        // PayPal retries a capture safely when this ID is reused for the same order
        // (PayPal API does not accept an empty body for the capture request)
        $response = $this->getClient(false)
            ->withHeaders(['PayPal-Request-Id' => 'shop-capture-'.$orderId])
            ->post('/v2/checkout/orders/'.$orderId.'/capture', ['' => 0]);

        if ($response->status() === 422) {
            // This can also mean the order was already captured, not that it failed.
            $order = $this->getClient()->get('/v2/checkout/orders/'.$orderId);

            if ($order->json('status') === 'COMPLETED') {
                return $this->reconcilePayPalOrder($orderId, $order);
            }

            // Preserve INSTRUMENT_DECLINED so the SDK can restart the approval flow.
            return response()->json($response->json());
        }

        $response->throw();

        return $this->reconcilePayPalOrder($orderId, $response);
    }

    protected function reconcilePayPalOrder(string $orderId, ?Response $order = null)
    {
        $order ??= $this->getClient()->get('/v2/checkout/orders/'.$orderId);
        $referenceId = $order->json('purchase_units.0.reference_id');
        $captureId = $order->json('purchase_units.0.payments.captures.0.id');

        $payment = Payment::find($referenceId);

        if ($payment === null || $payment->gateway_type !== $this->id
            || ! in_array($payment->transaction_id, [$orderId, $captureId], true)) {
            return response()->json(['message' => 'Unknown PayPal order'], 400);
        }

        if ($order->json('status') !== 'COMPLETED'
            || $order->json('purchase_units.0.payments.captures.0.status') !== 'COMPLETED') {
            return response()->json(['status' => 'payment_pending']);
        }

        if ($payment->isPending()) {
            $payment->update(['transaction_id' => $captureId]);
            $this->processPayment($payment);
        }

        return response()->json($order->json());
    }

    /**
     * Get the user and package IDs from the custom ID of a subscription created by the shop.
     */
    private function parseSubscriptionCustomId(mixed $customId): ?array
    {
        return is_string($customId) && substr_count($customId, '|') === 1
            ? explode('|', $customId)
            : null;
    }

    private function isFullRefund(Payment $payment, mixed $refundedAmount): bool
    {
        return is_numeric($refundedAmount) && round((float) $refundedAmount, 2) >= round($payment->price, 2);
    }

    /**
     * Find or create a PayPal subscription plan (and associated product) for the given package.
     */
    protected function findOrCreatePlan(Package $package): string
    {
        $metadata = $this->gateway->metadata()
            ->whereMorphedTo('model', $package)
            ->first();

        if ($metadata?->value === null) {
            $productId = $this->syncProduct($package);
            $planId = $this->syncPlan($package, $productId);

            $this->gateway->metadata()
                ->make(['value' => $productId.'|'.$planId])
                ->model()->associate($package)
                ->save();

            return $planId;
        }

        [$productId, $planId] = explode('|', $metadata->value);

        $productId = $this->syncProduct($package, $productId);
        $planId = $this->syncPlan($package, $productId, $planId);

        $metadata->update(['value' => $productId.'|'.$planId]);

        return $planId;
    }

    /**
     * Synchronize the plan with the PayPal API for the given productId, or create it if it does not exist.
     */
    protected function syncPlan(Package $package, string $productId, ?string $planId = null): string
    {
        $frequency = [
            // PayPal expects the interval unit to be singular in uppercase
            'interval_unit' => strtoupper(rtrim($package->subscriptionPeriodUnit(), 's')),
            'interval_count' => $package->subscriptionPeriodCount(),
        ];

        if ($planId === null) {
            $response = $this->getClient()->post('/v1/billing/plans', [
                'product_id' => $productId,
                'name' => $package->name,
                'description' => $package->short_description,
                'billing_cycles' => [
                    [
                        'frequency' => $frequency,
                        'tenure_type' => 'REGULAR',
                        'sequence' => 1,
                        'total_cycles' => 0,
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'value' => number_format($package->price, 2, '.', ''),
                                'currency_code' => currency(),
                            ],
                        ],
                    ],
                ],
                'payment_preferences' => [
                    'setup_fee' => [
                        'value' => 0,
                        'currency_code' => currency(),
                    ],
                ],
            ]);

            return $response->json('id');
        }

        $response = $this->getClient(false)->get('/v1/billing/plans/'.$planId);

        if ($response->status() === 404) {
            return $this->syncPlan($package, $productId);
        }

        $planFrequency = $response->throw()->json('billing_cycles.0.frequency');
        $planCurrency = $response->json('billing_cycles.0.pricing_scheme.fixed_price.currency_code');
        $planPrice = (float) $response->json('billing_cycles.0.pricing_scheme.fixed_price.value');

        if ($planFrequency !== $frequency || $planPrice !== $package->price || $planCurrency !== currency()) {
            return $this->syncPlan($package, $productId);
        }

        $this->getClient()->patch('/v1/billing/plans/'.$planId, [
            [
                'op' => 'replace',
                'path' => '/name',
                'value' => $package->name,
            ],
            [
                'op' => 'replace',
                'path' => '/description',
                'value' => $package->short_description,
            ],
        ]);

        return $planId;
    }

    /**
     * Synchronize the product with the PayPal API, or create it if it does not exist.
     */
    protected function syncProduct(Package $package, ?string $productId = null): string
    {
        if ($productId === null) {
            $response = $this->getClient()->post('/v1/catalogs/products', [
                'name' => $package->name,
                'description' => $package->short_description,
                'type' => 'DIGITAL',
            ]);

            return $response->json('id');
        }

        $res = $this->getClient(false)->patch('/v1/catalogs/products/'.$productId, [
            [
                'op' => 'replace',
                'path' => '/description',
                'value' => $package->short_description,
            ],
        ])->throwIf(fn (Response $res) => ! $res->successful() && $res->status() !== 404);

        return $res->successful() ? $productId : $this->syncProduct($package);
    }

    public function supportsSubscriptions()
    {
        return true;
    }

    public function view(): string
    {
        return 'shop::admin.gateways.methods.paypal-checkout';
    }

    public function rules(): array
    {
        return [
            'client-id' => ['required', 'string'],
            'secret' => ['required', 'string'],
            'webhook_id' => ['required', 'string'],
            'environment' => ['required', 'in:live,sandbox'],
        ];
    }

    /**
     * Verify the PayPal webhook request.
     */
    protected function verifyPayPalWebhook(Request $request): string
    {
        try {
            // Avoid using $request->json() as it might change slightly encoding
            // resulting in a failed verification.
            $webhookEvent = json_decode($request->getContent(), false, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            Log::warning('Invalid PayPal webhook JSON.', ['error' => $e->getMessage()]);

            throw new HttpResponseException(
                response()->json(['error' => 'Invalid PayPal webhook JSON.'], 400)
            );
        }

        $response = $this->getClient()->post('/v1/notifications/verify-webhook-signature', [
            'transmission_id' => $request->header('PayPal-Transmission-ID'),
            'transmission_time' => $request->header('PayPal-Transmission-Time'),
            'cert_url' => $request->header('PayPal-Cert-Url'),
            'auth_algo' => $request->header('PayPal-Auth-Algo'),
            'transmission_sig' => $request->header('PayPal-Transmission-Sig'),
            'webhook_id' => $this->gateway->data['webhook_id'],
            'webhook_event' => $webhookEvent,
        ]);

        if ($response->json('verification_status') !== 'SUCCESS') {
            Log::warning('Invalid PayPal webhook signature.');

            throw new HttpResponseException(
                response()->json(['error' => 'Invalid PayPal webhook signature.'], 401)
            );
        }

        return $request->json('event_type');
    }

    /**
     * Generate an OAuth 2.0 access token for authenticating with PayPal REST APIs.
     */
    protected function generateAccessToken(): string
    {
        $username = $this->gateway->data['client-id'];
        $password = $this->gateway->data['secret'];

        return $this->getBaseClient()
            ->asForm()
            ->withBasicAuth($username, $password)
            ->post('/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ])
            ->json('access_token');
    }

    private function getClient(bool $throw = true): PendingRequest
    {
        $clientId = $this->gateway->data['client-id'];
        $secret = $this->gateway->data['secret'];

        $token = Cache::remember(
            'shop.paypal.token.'.substr($clientId, 0, 5).substr($secret, 0, 5),
            now()->addHour(),
            fn () => $this->generateAccessToken()
        );

        return $this->getBaseClient($throw)->withToken($token);
    }

    private function getBaseClient(bool $throw = true): PendingRequest
    {
        $url = $this->gateway->data['environment'] === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        return Http::baseUrl($url)->throwIf($throw);
    }
}
