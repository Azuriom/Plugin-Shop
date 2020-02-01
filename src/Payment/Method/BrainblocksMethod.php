<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class BrainblocksMethod extends PaymentMethod
{
    /**
     * The payment method id name.
     *
     * @var string
     */
    protected $id = 'brainblocks';

    /**
     * The payment method display name.
     *
     * @var string
     */
    protected $name = 'NANO via Brainblocks';


    /**
     * The payment method image.
     *
     * @var string
     */
    protected $image = 'brainblocks.png';

    public function startPayment(Cart $cart, float $amount, string $currency)
    {
        $payment = $this->createPayment($cart, $amount, $currency);

        return view('shop::payments.brainblocks', [
            'public_key' => $this->gateway->data['public-key'],
            'currency' => strtolower($currency),
            'amount' => $amount,
            'payment_id' => $payment->id,
        ]);
    }

    public function notification(Request $request, ?string $rawPaymentId)
    {
        $payment = Payment::where('payment_id', '=', null)->findOrFail($request->input('id'));
        $token = $request->input('token');

        $url = "https://api.brainblocks.io/api/session/$token/verify";
        $client = new Client(['timeout' => 60]);

        $response = $client->get($url);
        $result = json_decode($response->getBody());

        if ($result->fulfilled !== true) {
            logger()->warning("[Shop] Invalid payment status for #{$token}: {$result->fulfilled} | received : {$result->received_rai}");

            return $this->invalidPayment($payment, $token, 'Invalid status');
        }

        if (strtolower($payment->currency) !== $result->currency || floatval($payment->price) !== floatval($result->amount)) {
            logger()->warning("[Shop] Invalid payment amount/currency for #{$token}: {$result->amount} {$result->currency}");

            return $this->invalidPayment($payment, $token, 'Invalid amount/currency');
        }

        if (strcasecmp($this->gateway->data['public-key'], $result->destination) !== 0) {
            logger()->warning("[Shop] Invalid nano address for #{$token}: {$result->destination}");

            return $this->invalidPayment($payment, $token, 'Invalid destination');
        }

        return $this->processPayment($payment, $token);
    }

    public function success(Request $request)
    {
        return view('shop::payments.success');
    }

    public function view()
    {
        return 'shop::admin.gateways.methods.brainblocks';
    }

    public function rules()
    {
        return [
            'public-key' => ['required', 'string'],
        ];
    }
}