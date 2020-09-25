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
     *
     * @var Gateway|null
     */
    protected $gateway;

    /**
     * Create a new method instance.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Gateway|null  $gateway
     */
    public function __construct(?Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Start a new payment with this method.
     *
     * @param  \Azuriom\Plugin\Shop\Cart\Cart  $cart
     * @param  float  $amount
     * @param  string  $currency
     * @return \Illuminate\Http\Response
     */
    abstract public function startPayment(Cart $cart, float $amount, string $currency);

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $paymentId
     * @return \Illuminate\Http\Response
     */
    abstract public function notification(Request $request, ?string $paymentId);

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    abstract public function success(Request $request);

    /**
     * @param  \Illuminate\Http\Request  $request
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

    public function id()
    {
        return $this->id;
    }

    public function name()
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

        return response()->json(['status' => false, 'message' => $message]);
    }

    protected function createPayment(Cart $cart, float $price, string $currency, string $paymentId = null)
    {
        $payment = Payment::create([
            'price' => $price,
            'currency' => $currency,
            'gateway_type' => $this->id,
            'status' => 'pending',
            'transaction_id' => $paymentId,
        ]);

        foreach ($cart->content() as $item) {
            $payment->items()
                ->make([
                    'name' => $item->name(),
                    'price' => $item->price(),
                    'quantity' => $item->quantity,
                ])
                ->buyable()->associate($item->buyable())
                ->save();
        }

        $payment->coupons()->sync($cart->coupons());

        return $payment;
    }

    protected function processPayment(?Payment $payment, string $paymentId = null)
    {
        if ($payment === null) {
            return response()->json(['status' => false, 'message' => 'Unable to retrieve the payment']);
        }

        if ($payment->isCompleted()) {
            return response()->json(['status' => true, 'message' => 'Payment already completed']);
        }

        if (! $payment->isPending()) {
            return response()->json(['status' => false, 'message' => 'Invalid payment status: '.$payment->status]);
        }

        if ($paymentId !== null) {
            $payment->fill(['transaction_id' => $paymentId]);
        }

        $payment->deliver();

        return response()->json(['status' => true]);
    }

    protected function errorResponse()
    {
        return redirect()->route('shop.cart.index')->with('error', trans('shop::messages.payment.error'));
    }

    protected function getPurchaseDescription(int $id)
    {
        return trans('shop::messages.payment.info', [
            'id' => $id,
            'website' => site_name(),
        ]);
    }
}
