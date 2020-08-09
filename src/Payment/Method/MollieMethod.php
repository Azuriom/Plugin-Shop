<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Request;
use Mollie\Api\MollieApiClient;

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

    public function startPayment(Cart $cart, float $amount, string $currency)
    {
        $mollie = $this->createMollieClient();

        $payment = $this->createPayment($cart, $amount, $currency);

        $molliePayment = $mollie->payments->create([
            'amount' => [
                'currency' => $currency,
                'value' => number_format($amount, 2),
            ],
            'description' => $this->getPurchaseDescription($payment->id),
            'redirectUrl' => route('shop.payments.success', $this->id),
            'webhookUrl' => route('shop.payments.notification', ['gateway' => 'mollie']),
            'metadata' => [
                'order_id' => $payment->id,
            ],
        ]);

        $payment->update(['transaction_id' => $molliePayment->id]);

        return redirect()->away($molliePayment->getCheckoutUrl());
    }

    public function notification(Request $request, ?string $paymentId)
    {
        $mollie = $this->createMollieClient();
        $molliePayment = $mollie->payments->get($request->input('id'));

        if (! $molliePayment->isPaid() || $molliePayment->hasRefunds() || $molliePayment->hasChargebacks()) {
            return response()->json(['status' => false, 'message' => 'Invalid Mollie payment status']);
        }

        $orderId = $molliePayment->metadata->order_id;
        $payment = Payment::find($orderId);

        return $this->processPayment($payment, $orderId);
    }

    public function success(Request $request)
    {
        return view('shop::payments.success');
    }

    public function view()
    {
        return 'shop::admin.gateways.methods.mollie';
    }

    public function rules()
    {
        return [
            'key' => ['required', 'string', 'starts_with:test_,live_', 'min:30'],
        ];
    }

    protected function createMollieClient()
    {
        $mollie = new MollieApiClient();
        $mollie->setApiKey($this->gateway->data['key']);

        return $mollie;
    }
}
