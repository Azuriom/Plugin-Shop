<?php

namespace Azuriom\Plugin\Shop\Payment;

use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Gateway;
use Azuriom\Plugin\Shop\Models\Package;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Models\Subscription;
use Illuminate\Http\Request;
use InvalidArgumentException;

abstract class PaymentMethod
{
    /**
     * The payment method name.
     *
     * @var string
     */
    protected $name;

    /**
     * The associated gateway.
     */
    protected ?Gateway $gateway;

    /**
     * Create a new method instance.
     */
    public function __construct(?Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Start a new payment with this method and return the payment response to the user (redirect, form, ...).
     *
     * @return \Illuminate\Http\Response
     */
    abstract public function startPayment(Cart $cart, float $amount, string $currency);

    /**
     * Start a new subscription with this method.
     *
     * @return \Illuminate\Http\Response
     */
    public function startSubscription(User $user, Package $package)
    {
        throw new InvalidArgumentException('This payment method does not support subscriptions.');
    }

    public function cancelSubscription(Subscription $subscription): void
    {
        throw new InvalidArgumentException('This payment method does not support subscriptions.');
    }

    /**
     * Handle a payment notification request sent by the payment gateway and return a response.
     *
     * @return \Illuminate\Http\Response
     */
    abstract public function notification(Request $request, ?string $paymentId);

    /**
     * Return the payment success response.
     *
     * @return \Illuminate\Http\Response
     */
    public function success(Request $request)
    {
        return to_route('shop.home')
            ->with('success', trans('shop::messages.payment.success'));
    }

    /**
     * Return the payment failure response.
     *
     * @return \Illuminate\Http\Response
     */
    public function failure(Request $request)
    {
        return $this->errorResponse();
    }

    /**
     * Get the view for the gateway config in the admin panel.
     *
     * @return string
     */
    abstract public function view();

    /**
     * Get the validation rules for the gateway config in the admin panel.
     *
     * @return array
     */
    abstract public function rules();

    /**
     * Get the payment method image.
     */
    public function image()
    {
        return asset('plugins/shop/img/payments/'.($this->image ?? ($this->id().'.svg')));
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * Return whether this payment method supports payment with user-defined amounts,
     * or if it only supports amounts defined by the payment gateway.
     *
     * @return bool
     */
    public function hasFixedAmount()
    {
        return false;
    }

    /**
     * Return whether this payment method supports subscriptions.
     *
     * @return bool
     */
    public function supportsSubscriptions()
    {
        return false;
    }

    protected function invalidPayment(Payment $payment, string $paymentId, string $message)
    {
        $payment->update(['status' => 'error', 'transaction_id' => $paymentId]);

        return response()->json([
            'status' => false,
            'message' => $message,
        ]);
    }

    /**
     * Create a new pending payment for the given cart, associated with this payment method.
     */
    protected function createPayment(Cart $cart, float $price, string $currency, ?string $paymentId = null): Payment
    {
        // Clear expired pending payments before creating a new one
        Payment::purgePendingPayments();

        return PaymentManager::createPayment($cart, $price, $currency, $this->id, $paymentId);
    }

    /**
     * Create a new active subscription for the given user and package.
     */
    protected function createSubscription(User $user, Package $package, string $subscriptionId, ?float $price = null, ?string $currency = null)
    {
        return $package->subscriptions()->create([
            'user_id' => $user->id,
            'subscription_id' => $subscriptionId,
            'gateway_type' => $this->id,
            'status' => 'active',
            'price' => $price ?? $package->price,
            'currency' => $currency ?? currency(),
        ]);
    }

    protected function renewSubscription(Subscription $subscription, string $transactionId, bool $initial = false)
    {
        if ($subscription->payments()->where('transaction_id', $transactionId)->exists()) {
            return response()->json(['status' => 'duplicate_id']);
        }

        $payment = $subscription->addRenewalPayment($transactionId);

        $payment->deliver(! $initial);

        return response()->json([
            'status' => $initial ? 'subscription_created' : 'subscription_renewed',
        ]);
    }

    /**
     * Try to process the given payment, and return a response with the result.
     * If a payment ID is provided, it will be used to update the payment transaction ID.
     */
    protected function processPayment(?Payment $payment, ?string $paymentId = null)
    {
        if ($payment === null) {
            logger()->warning('[Shop] Invalid payment for #'.$paymentId);

            return response()->json([
                'status' => false,
                'message' => 'Unable to retrieve the payment',
            ], 400);
        }

        if ($payment->isCompleted()) {
            return response()->json([
                'status' => true,
                'message' => 'Payment already completed',
            ]);
        }

        if (! $payment->isPending()) {
            logger()->warning("[Shop] Invalid payment status for #{$payment->id}: ".$payment->status);

            return response()->json([
                'status' => false,
                'message' => 'Invalid payment status: '.$payment->status,
            ]);
        }

        if ($paymentId !== null) {
            $payment->fill(['transaction_id' => $paymentId]);
        }

        $payment->deliver();

        return response()->json(['status' => true]);
    }

    /**
     * Mark the payment as chargebacked, and dispatch the necessary commands.
     */
    protected function processChargeback(Payment $payment)
    {
        return $this->processRefund($payment, true);
    }

    /**
     * Mark the payment as refunded, and dispatch the necessary commands.
     */
    protected function processRefund(Payment $payment, bool $isChargeback = false)
    {
        if ($payment->status === 'refunded' || $payment->status === 'chargeback') {
            return response()->json([
                'status' => true,
                'message' => 'Payment already refunded',
            ]);
        }

        if (! $payment->isCompleted()) {
            logger()->warning("[Shop] Invalid payment status for #{$payment->id}: ".$payment->status);

            return response()->json([
                'status' => false,
                'message' => 'Invalid payment status: '.$payment->status,
            ]);
        }

        $payment->update(['status' => $isChargeback ? 'chargeback' : 'refunded']);

        $payment->dispatchCommands($isChargeback ? 'chargeback' : 'refund');

        if (($webhook = setting('shop.webhook')) !== null) {
            rescue(fn () => $payment->createRefundDiscordWebhook($isChargeback)->send($webhook));
        }

        return response()->json(['status' => true]);
    }

    /**
     * Return a redirect response with a generic payment error message.
     */
    protected function errorResponse()
    {
        return to_route('shop.cart.index')->with('error', trans('shop::messages.payment.error'));
    }

    protected function getPurchaseDescription(int $id): string
    {
        return trans('shop::messages.payment.info', [
            'id' => $id,
            'website' => site_name(),
        ]);
    }

    protected function getSubscriptionDescription(User $user, Package $package): string
    {
        return trans('shop::messages.payment.subscription', [
            'user' => $user->id,
            'package' => $package->name,
        ]);
    }
}
