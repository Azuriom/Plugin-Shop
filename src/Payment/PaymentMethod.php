<?php

namespace Azuriom\Plugin\Shop\Payment;

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Gateway;
use Azuriom\Plugin\Shop\Models\Payment;
use Illuminate\Http\Request;

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
     * Handle a payment notification request.
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
    protected function createPayment(Cart $cart, float $price, string $currency, string $paymentId = null): Payment
    {
        // Clear expired pending payments before creating a new one
        Payment::clearExpiredPayments();

        return PaymentManager::createPayment($cart, $price, $currency, $this->id, $paymentId);
    }

    protected function processPayment(?Payment $payment, string $paymentId = null)
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
}
