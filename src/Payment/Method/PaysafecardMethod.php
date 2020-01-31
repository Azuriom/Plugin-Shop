<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use RuntimeException;

class PaysafecardMethod extends PaymentMethod
{
    /**
     * The paysafecard available environments.
     *
     * @var array
     */
    protected const ENVIRONMENTS = [
        'test', 'production'
    ];

    /**
     * The payment method id name.
     *
     * @var string
     */
    protected $id = 'paysafecard';

    /**
     * The payment method display name.
     *
     * @var string
     */
    protected $name = 'paysafecard';

    public function startPayment(Cart $cart, float $amount, string $currency)
    {
        $successUrl = route('shop.payments.success', [
            'gateway' => 'paysafecard',
            'paymentId' => rawurlencode('{payment_id}')
        ]);

        $failureUrl = route('shop.payments.failure', [
            'gateway' => 'paysafecard',
            'paymentId' => rawurlencode('{payment_id}')
        ]);

        $options = [
            'type' => 'PAYSAFECARD',
            'amount' => $amount,
            'currency' => $currency,
            'redirect' => [
                'success_url' => $successUrl,
                'failure_url' => $failureUrl,
            ],
            'notification_url' => route('shop.payments.notification', [$this->id, rawurlencode('{payment_id}')]),
            'customer' => [
                'id' => Auth::id()
            ]
        ];

        $response = $this->sendRequest('POST', '', $options);

        if ($response === null) {
            throw new RuntimeException('Invalid response from paysafecard');
        }

        $this->createPayment($cart, $amount, $currency, $response->id);

        return redirect()->away($response->redirect->auth_url);
    }

    public function notification(Request $request, ?string $paymentId)
    {
        abort_if($paymentId === null, 404);

        return response()->json($this->processPscPayment($paymentId));
    }

    public function processPscPayment(string $paymentId)
    {
        $payment = Payment::where('payment_id', $paymentId)->first();

        $response = $this->retrievePayment($paymentId);

        if ($response->status !== 'AUTHORIZED') {
            return ['status' => 'error', 'message' => 'Invalid payment response'];
        }

        $response = $this->capturePayment($paymentId);

        if ($response->status !== 'SUCCESS') {
            return ['status' => 'error', 'message' => 'Invalid capture response'];
        }

        payment_manager()->deliverPayment($payment);

        return ['status' => 'success'];
    }

    public function success(Request $request)
    {
        $paymentId = $request->input('paymentId');

        if ($paymentId === null) {
            return $this->errorResponse();
        }

        $payment = $this->processPscPayment($paymentId);

        if ($payment['status'] !== true) {
            return $this->errorResponse();
        }

        return view('shop.payments.success');
    }

    public function view()
    {
        return 'shop::admin.gateways.methods.paysafecard';
    }

    public function rules()
    {
        return [
            'key' => ['required', 'string'],
            'environment' => ['required', Rule::in(self::ENVIRONMENTS)],
        ];
    }

    private function sendRequest(string $method, string $endpoint, array $params = [], array $headers = [])
    {
        $domain = $this->gateway->data['environment'] === 'PRODUCTION' ? 'api' : 'apitest';
        $url = "https://{$domain}.paysafecard.com/v1/payments/{$endpoint}";

        $client = new Client(['timeout' => 60]);

        $request = new GuzzleRequest($method, $url, $headers + [
                'Authorization: Basic '.base64_encode($this->gateway->data['key']),
                'Content-Type: application/json',
            ], $params ? json_encode($params) : null);

        $response = $client->send($request);

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        return json_decode($response->getBody()->getContents());
    }

    private function capturePayment(string $paymentId)
    {
        return $this->sendRequest('POST', $paymentId.'/capture', ['id' => $paymentId]);
    }

    private function retrievePayment(string $paymentId)
    {
        return $this->sendRequest('GET', $paymentId);
    }

    public static function environments()
    {
        return self::ENVIRONMENTS;
    }
}
