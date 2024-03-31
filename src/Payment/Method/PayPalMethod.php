<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PayPalMethod extends PaymentMethod
{
    /**
     * The payment method id name.
     *
     * @var string
     */
    protected $id = 'paypal';

    /**
     * The payment method display name.
     *
     * @var string
     */
    protected $name = 'PayPal';

    public function startPayment(Cart $cart, float $amount, string $currency)
    {
        $payment = $this->createPayment($cart, $amount, $currency);

        $attributes = [
            'cmd' => '_xclick',
            'charset' => 'utf-8',
            'business' => $this->gateway->data['email'],
            'amount' => $amount,
            'currency_code' => $currency,
            'item_name' => $this->getPurchaseDescription($payment->id),
            'quantity' => 1,
            'no_shipping' => 1,
            'no_note' => 1,
            'return' => route('shop.payments.success', $this->id),
            'cancel_return' => route('shop.cart.index'),
            'notify_url' => route('shop.payments.notification', $this->id),
            'custom' => $payment->id,
            'bn' => 'Azuriom',
        ];

        return redirect()->away('https://www.paypal.com/cgi-bin/webscr?'.Arr::query($attributes));
    }

    public function notification(Request $request, ?string $rawPaymentId)
    {
        $data = ['cmd' => '_notify-validate'] + $request->all();

        $response = Http::asForm()->post('https://ipnpb.paypal.com/cgi-bin/webscr', $data);

        if ($response->body() !== 'VERIFIED') {
            return response()->json('Invalid response from PayPal', 401);
        }

        $paymentId = $request->input('txn_id');
        $amount = $request->input('mc_gross');
        $currency = $request->input('mc_currency');
        $status = $request->input('payment_status');
        $caseType = $request->input('case_type');
        $receiverEmail = Str::lower($request->input('receiver_email'));

        if ($status === 'Canceled_Reversal' || $caseType !== null) {
            return response()->noContent();
        }

        if ($status === 'Reversed') {
            $parentTransactionId = $request->input('parent_txn_id');

            $payment = Payment::firstWhere('transaction_id', $parentTransactionId);

            return $this->processChargeback($payment);
        }

        $payment = Payment::findOrFail($request->input('custom'));

        if ($status === 'Pending') {
            $payment->update(['status' => 'pending', 'transaction_id' => $paymentId]);
            logger()->info('[Shop] Pending payment for #'.$paymentId);

            return response()->noContent();
        }

        if ($status === 'Refunded') {
            return $this->processRefund($payment);
        }

        if ($status !== 'Completed') {
            logger()->warning("[Shop] Invalid payment status for #{$paymentId}: {$status}");

            return $this->invalidPayment($payment, $paymentId, 'Invalid status');
        }

        if ($currency !== $payment->currency || $amount < $payment->price) {
            logger()->warning("[Shop] Invalid payment amount or currency for #{$paymentId}: {$amount} {$currency}.");

            return $this->invalidPayment($payment, $paymentId, 'Invalid amount/currency');
        }

        $email = Str::lower($this->gateway->data['email']);

        if ($receiverEmail !== $email) {
            logger()->warning("[Shop] Invalid email for #{$paymentId}: expected {$email} but got {$receiverEmail}.");

            return $this->invalidPayment($payment, $paymentId, 'Invalid email');
        }

        return $this->processPayment($payment, $paymentId);
    }

    public function view(): string
    {
        return 'shop::admin.gateways.methods.paypal';
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
