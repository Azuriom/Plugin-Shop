<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Gateway;
use Azuriom\Plugin\Shop\Models\Package;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Models\Subscription;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Request;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Customer;
use Mollie\Api\Resources\Payment as MolliePayment;

class MollieMethod extends PaymentMethod
{
    /**
     * The payment method id name.
     *
     * @var string
     */
    protected $id = 'mollie';

    /**
     * The payment method display name.
     *
     * @var string
     */
    protected $name = 'Mollie';

    protected MollieApiClient $mollie;

    public function __construct(?Gateway $gateway)
    {
        parent::__construct($gateway);

        if ($gateway?->data !== null) {
            $this->mollie = $this->createMollieClient();
        }
    }

    public function startPayment(Cart $cart, float $amount, string $currency)
    {
        $payment = $this->createPayment($cart, $amount, $currency);

        $molliePayment = $this->mollie->payments->create([
            'amount' => [
                'currency' => $currency,
                'value' => number_format($amount, 2),
            ],
            'description' => $this->getPurchaseDescription($payment->id),
            'redirectUrl' => route('shop.payments.success', [
                'gateway' => $this->id,
                'id' => $payment->id,
            ]),
            'cancelUrl' => route('shop.cart.index'),
            'webhookUrl' => route('shop.payments.notification', ['gateway' => $this->id]),
            'metadata' => [
                'order_id' => $payment->id,
            ],
        ]);

        $payment->update(['transaction_id' => $molliePayment->id]);

        return redirect()->away($molliePayment->getCheckoutUrl());
    }

    public function startSubscription(User $user, Package $package)
    {
        $customer = $this->findOrCreateCustomer($user);

        $molliePayment = $this->mollie->payments->create([
            'amount' => [
                'currency' => currency(),
                'value' => number_format($package->price, 2),
            ],
            'description' => $this->getSubscriptionDescription($user, $package),
            'customerId' => $customer->id,
            'sequenceType' => 'first',
            'redirectUrl' => route('shop.payments.success', $this->id),
            'cancelUrl' => route('shop.categories.show', $package->category),
            'webhookUrl' => route('shop.payments.notification', ['gateway' => $this->id]),
            'metadata' => [
                'user' => $user->id,
                'package' => $package->id,
                'mode' => 'subscription_first',
            ],
        ]);

        return redirect()->away($molliePayment->getCheckoutUrl());
    }

    public function cancelSubscription(Subscription $subscription): void
    {
        $subscriptionId = $subscription->subscription_id;
        $customer = $this->findCustomer($subscription->user);

        if ($customer !== null) {
            $this->mollie->subscriptions->cancelFor($customer, $subscriptionId);
        }
    }

    public function success(Request $request)
    {
        $paymentId = $request->input('id');

        if ($paymentId === null) {
            return to_route('shop.profile');
        }

        $payment = Payment::findOrFail($paymentId);

        try {
            $molliePayment = $this->mollie->payments->get($payment->transaction_id);

            if ($molliePayment === null) {
                return to_route('shop.home')->with('error', trans('shop::messages.payment.error'));
            }

            return match ($molliePayment->status) {
                'pending' => to_route('shop.home')->with('success', trans('shop::messages.payment.pending')),
                'paid' => to_route('shop.home')->with('success', trans('shop::messages.payment.success')),
                'expired', 'failed' => to_route('shop.home')->with('error', trans('shop::messages.payment.error')),
                default => to_route('shop.home'),
            };
        } catch (ApiException $e) {
            return to_route('shop.home')->with('error', trans('messages.status.error', [
                'error' => $e->getMessage(),
            ]));
        }
    }

    public function notification(Request $request, ?string $paymentId)
    {
        $id = $request->input('id');
        $molliePayment = $this->mollie->payments->get($id);

        if ($molliePayment->metadata?->mode ?? '' === 'subscription_first') {
            $package = Package::findOrFail($molliePayment->metadata->package);
            $user = User::findOrFail($molliePayment->metadata->user);

            return $this->startMollieSubscription($molliePayment, $user, $package);
        }

        if ($molliePayment->subscriptionId !== null) {
            return $this->renewMollieSubscription($id, $molliePayment->subscriptionId);
        }

        $payment = Payment::find($molliePayment->metadata->order_id);

        if ($molliePayment->hasChargebacks()) {
            return $this->processChargeback($payment);
        }

        if ($molliePayment->hasRefunds()) {
            return $this->processRefund($payment);
        }

        if (! $molliePayment->isPaid()) {
            return response()->json(['status' => false, 'message' => 'Invalid Mollie payment status'], 400);
        }

        return $this->processPayment($payment);
    }

    protected function startMollieSubscription(MolliePayment $payment, User $user, Package $package)
    {
        $subscription = $this->mollie->subscriptions->createForId($payment->customerId, [
            'amount' => [
                'currency' => currency(),
                'value' => number_format($package->price, 2),
            ],
            'interval' => $package->billing_period,
            'startDate' => now()->add($package->billing_period)->toDateString(),
            'description' => $this->getSubscriptionDescription($user, $package),
            'webhookUrl' => route('shop.payments.notification', ['gateway' => $this->id]),
        ]);

        $sub = $this->createSubscription($user, $package, $subscription->id);

        return $this->renewSubscription($sub, $payment->id, true);
    }

    protected function renewMollieSubscription(string $paymentId, string $subscriptionId)
    {
        $subscription = Subscription::where('subscription_id', $subscriptionId)->firstOrFail();

        return $this->renewSubscription($subscription, $paymentId);
    }

    public function supportsSubscriptions()
    {
        return true;
    }

    public function view(): string
    {
        return 'shop::admin.gateways.methods.mollie';
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'starts_with:test_,live_', 'min:30'],
        ];
    }

    protected function findCustomer(User $user): ?Customer
    {
        $customerId = $this->gateway->metadata()
            ->whereMorphedTo('model', $user)
            ->value('value');

        return $customerId !== null ? $this->mollie->customers->get($customerId) : null;
    }

    protected function findOrCreateCustomer(User $user): Customer
    {
        $customer = $this->findCustomer($user);
        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'metadata' => ['user_id' => $user->id],
        ];

        if ($customer !== null) {
            $this->mollie->customers->update($customer->id, $data);

            return $customer;
        }

        $customer = $this->mollie->customers->create($data);

        $this->gateway->metadata()
            ->make(['value' => $customer->id])
            ->model()->associate($user)
            ->save();

        return $customer;
    }

    protected function createMollieClient(): MollieApiClient
    {
        return (new MollieApiClient())->setApiKey($this->gateway->data['key']);
    }
}
