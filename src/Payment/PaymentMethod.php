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
     * Start a new payment with this method.
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
     * Get the view for the gateway config.
     *
     * @return string
     */
    abstract public function view();

    /**
     * Get the validation rules for the gateway config.
     *
     * @return array
     */
    abstract public function rules();

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

    protected function processChargeback(Payment $payment)
    {
        return $this->processRefund($payment, true);
    }

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

    protected function errorResponse()
    {
        return to_route('shop.cart.index')->with('error', trans('shop::messages.payment.error'));
    }

    protected function getPurchaseDescription(int $id)
    {
        return trans('shop::messages.payment.info', [
            'id' => $id,
            'website' => site_name(),
        ]);
    }
}
