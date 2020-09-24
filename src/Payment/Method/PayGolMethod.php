<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\Countries;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Paygol\Models\Payer;
use Paygol\Models\RedirectUrls;
use Paygol\Notification;
use Paygol\Webcheckout;

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
        $user = auth()->user();

        $paygol = $this->createWebcheckout();

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setRedirects(
            route('shop.payments.success', $this->id),
            route('shop.payments.failure', $this->id)
        );
        $paygol->setRedirects($redirectUrls);

        $paygol->setCountry($this->gateway->data['country']);
        $paygol->setPrice($amount, $currency);

        $payer = new Payer();
        $payer->setEmail($user->email);
        $payer->setPersonalID($user->id);
        $paygol->setPayer($payer);
        $paygol->setName($this->getPurchaseDescription($payment->id));
        $paygol->setCustom($payment->id);

        $payGolPayment = $paygol->createPayment();

        return redirect()->away($payGolPayment['data']['payment_method_url']);
    }

    public function notification(Request $request, ?string $paymentId)
    {
        $ipn = $this->createNotification();

        $ipn->validate();

        $params = $ipn->getParams();

        $transactionId = $params['transaction_id'];

        if ($params['status'] !== 'completed') {
            return response()->json(['status' => false, 'message' => 'Invalid PayGol payment status']);
        }

        $payment = Payment::find($params['custom']);

        return $this->processPayment($payment, $transactionId);
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
            'country' => ['required', Rule::in(Countries::codes())],
        ];
    }

    public function countries()
    {
        return Countries::countries();
    }

    private function createWebcheckout()
    {
        return new Webcheckout($this->gateway->data['service-id'], $this->gateway->data['key']);
    }

    private function createNotification()
    {
        return new Notification($this->gateway->data['service-id'], $this->gateway->data['key']);
    }
}
