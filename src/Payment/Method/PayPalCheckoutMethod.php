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
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

        $items = $cart->itemsPrice()->map(fn (array $data) => [
            'name' => $data['item']->name(),
            'quantity' => $data['item']->quantity,
            'description' => $data['item']->buyable()->getDescription(),
            'category' => 'DIGITAL_GOODS',
            'unit_amount' => [
                'currency_code' => $currency,
                'value' => number_format($data['unit_price'], 2),
            ],
        ]);

        $response = $this->getClient()->post('/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $payment->id,
                    'description' => $this->getPurchaseDescription($payment->id),
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => number_format($amount, 2),
                    ],
                ],
            ],
            'items' => $items,
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
            'description' => $this->getPurchaseDescription($payment->id),
            'currency' => $currency,
            'successUrl' => route('shop.payments.success', $this->id),
            'captureUrl' => route('shop.payments.notification', $this->id),
        ]);
    }

    public function startSubscription(User $user, Package $package)
    {
        $planId = $this->findOrCreatePlan($package);

        $response = $this->getClient()->post('/v1/billing/subscriptions', [
            'custom_id' => $user->id.'|'.$package->id,
            'plan_id' => $planId,
            'subscriber' => [
                'email_address' => $user->email,
            ],
            'application_context' => [
                'shipping_preference' => 'NO_SHIPPING',
                'return_url' => route('shop.payments.success', $this->id),
                'cancel_url' => route('shop.cart.index'),
            ],
        ]);

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
            [$userId, $packageId] = explode('|', $request->json('resource.custom_id'));
            $subscriptionId = $request->json('resource.id');

            // If the subscription was created after the first payment, process it now
            $paymentId = Cache::pull('shop.paypal.subscription.'.$subscriptionId);

            if ($paymentId !== null) {
                return $this->createPayPalSubscription($userId, $packageId, $subscriptionId, $paymentId);
            }

            // Wait for the first payment to process the subscription
            Log::info('Waiting payment for PayPal subscription '.$subscriptionId.', for user '.$userId.' and package '.$packageId);

            return response()->json(['status' => 'subscription_pending']);
        }

        if ($type === 'PAYMENT.SALE.COMPLETED') {
            $subscriptionId = $request->json('resource.billing_agreement_id');
            $paymentId = $request->json('resource.id');

            $subscription = Subscription::firstWhere('subscription_id', $subscriptionId);

            if ($subscription !== null) {
                return $this->renewSubscription($subscription, $paymentId);
            }

            $response = $this->getClient()->get('/v1/billing/subscriptions/'.$subscriptionId);
            [$userId, $packageId] = explode('|', $response->json('custom_id'));

            return $this->createPayPalSubscription($userId, $packageId, $subscriptionId, $paymentId);
        }

        if ($type === 'CUSTOMER.DISPUTE.CREATED') {
            foreach ($request->json('resource.disputed_transactions') as $dispute) {
                $transactionId = Arr::get($dispute, 'seller_transaction_id');
                $payment = Payment::firstWhere('transaction_id', $transactionId);

                $this->processChargeback($payment);
            }
        }

        if ($type === 'PAYMENT.CAPTURE.REFUNDED' || $type === 'PAYMENT.CAPTURE.REVERSED') {
            $links = collect($request->json('resource.links'));
            $transactionId = Str::afterLast(Arr::get($links->firstWhere('rel', 'up'), 'href'), '/');
            $payment = Payment::firstWhere('transaction_id', $transactionId);

            return $this->processRefund($payment);
        }

        return response()->json(['status' => 'ok']);
    }

    protected function createPayPalSubscription(string $userId, string $packageId, string $subscription, string $payment)
    {
        $user = User::find($userId);
        $package = Package::find($packageId);
        $subscription = $this->createSubscription($user, $package, $subscription);

        return $this->renewSubscription($subscription, $payment, true);
    }

    protected function capturePayPalOrder(string $paymentId)
    {
        // Seems like the PayPal API does not accept an empty body for the capture request
        $response = $this->getClient()->post('/v2/checkout/orders/'.$paymentId.'/capture', ['' => 0]);

        if ($response->json('status') === 'COMPLETED') {
            $payment = Payment::firstWhere('transaction_id', $paymentId);
            $transactionId = $response->json('purchase_units.0.payments.captures.0.id');

            $payment->update(['transaction_id' => $transactionId]);

            $this->processPayment($payment);
        }

        return response()->json($response->json());
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
                                'value' => number_format($package->price, 2),
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
        $response = $this->getClient()->post('/v1/notifications/verify-webhook-signature', [
            'transmission_id' => $request->header('PayPal-Transmission-ID'),
            'transmission_time' => $request->header('PayPal-Transmission-Time'),
            'cert_url' => $request->header('PayPal-Cert-Url'),
            'auth_algo' => $request->header('PayPal-Auth-Algo'),
            'transmission_sig' => $request->header('PayPal-Transmission-Sig'),
            'webhook_id' => $this->gateway->data['webhook_id'],
            'webhook_event' => $request->json()->all(),
        ]);

        if ($response->json('verification_status') !== 'SUCCESS') {
            Log::warning('Invalid PayPal webhook signature.');

            abort(400, 'Invalid PayPal webhook signature.');
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
