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
            'return' => route('shop.cart.index'),
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
            return response()->json('Invalid response');
        }

        $paymentId = $request->input('txn_id');
        $amount = (float) $request->input('mc_gross');
        $currency = $request->input('mc_currency');
        $status = $request->input('payment_status');
        $receiverEmail = Str::lower($request->input('receiver_email'));

        $payment = Payment::findOrFail($request->input('custom'));

        if ($status === 'Pending') {
            $payment->update(['status' => 'pending', 'transaction_id' => $paymentId]);

            return response()->noContent();
        }

        if ($status !== 'Completed') {
            logger()->warning("[Shop] Invalid payment status for #{$paymentId}: {$status}");

            return $this->invalidPayment($payment, $paymentId, 'Invalid status');
        }

        if ($payment->currency !== $currency || $payment->price !== $amount) {
            logger()->warning("[Shop] Invalid payment amount/currency for #{$paymentId}: {$amount} {$currency}");

            return $this->invalidPayment($payment, $paymentId, 'Invalid amount/currency');
        }

        $email = Str::lower($this->gateway->data['email']);

        if ($email !== $receiverEmail) {
            logger()->warning("[Shop] Invalid email for #{$paymentId}: expected {$email} but got {$receiverEmail}.");

            return $this->invalidPayment($payment, $paymentId, 'Invalid email');
        }

        return $this->processPayment($payment, $paymentId);
    }

    public function success(Request $request)
    {
        return view('shop::payments.success');
    }

    public function view()
    {
        return 'shop::admin.gateways.methods.paypal';
    }

    public function rules()
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
