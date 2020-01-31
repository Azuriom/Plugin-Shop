<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Request;
use Paygol\API;
use Paygol\Models\Payer;
use Paygol\Models\RedirectUrls;
use Paygol\Notification;

class PayGolMethod extends PaymentMethod
{
    /**
     * The payment method id name.
     *
     * @var string
     */
    protected $id = 'paygol';

    /**
     * The payment method display name.
     *
     * @var string
     */
    protected $name = 'PayGol';

    /**
     * The payment method image.
     *
     * @var string
     */
    protected $image = 'paygol.png';

    public function startPayment(Cart $cart, float $amount, string $currency)
    {
        $payment = $this->createPayment($cart, $amount, $currency);

        $paygol = $this->createApi();

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setRedirects(
            route('shop.payments.success'),
            route('shop.payments.failure')
        );
        $paygol->setRedirects($redirectUrls);

        $paygol->setPrice($amount, $currency);

        $payer = new Payer();
        $payer->setEmail(auth()->user()->email);
        $payer->setPersonalID(auth()->id());
        $paygol->setPayer($payer);
        $paygol->setCustom($payment->id);

        $payGolPayment = $paygol->createPayment();

        return redirect()->away($payGolPayment['data']['payment_method_url']);
    }

    public function notification(Request $request, ?string $paymentId)
    {
        $ipn = $this->createNotification();

        $ipn->validate();

        $params = $ipn->getParams();

        $paymentId = $params['transaction_id'];

        if ($params['status'] !== 'completed') {
            return response()->json(['status' => 'error', 'message' => 'Invalid PayGol payment status']);
        }

        $payment = Payment::find($params['custom']);

        return $this->processPayment($payment, $paymentId);
    }

    public function success(Request $request)
    {
        return view('shop::payments.success');
    }

    public function view()
    {
        return 'shop::admin.gateways.methods.paygol';
    }

    public function rules()
    {
        return [
            'key' => ['required', 'string'],
            'service-id' => ['required', 'string'],
        ];
    }

    private function createApi()
    {
        return new API($this->gateway->data['service-id'], $this->gateway->data['key']);
    }

    private function createNotification()
    {
        return new Notification($this->gateway->data['service-id'], $this->gateway->data['key']);
    }
}
