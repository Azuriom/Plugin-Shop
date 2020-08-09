<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PaysafecardMethod extends PaymentMethod
{
    /**
     * The paysafecard available environments.
     *
     * @var array
     */
    protected const ENVIRONMENTS = [
        'test', 'production',
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
        $successUrl = route('shop.payments.success', $this->id);
        $failureUrl = route('shop.payments.failure', $this->id);
        $notificationUrl = route('shop.payments.notification', [$this->id, '%id%']);

        $response = $this->prepareRequest()->post('', [
            'type' => 'PAYSAFECARD',
            'amount' => $amount,
            'currency' => $currency,
            'redirect' => [
                'success_url' => "{$successUrl}?id={payment_id}",
                'failure_url' => "{$failureUrl}?id={payment_id}",
            ],
            'notification_url' => str_replace('%id%', '{payment_id}', $notificationUrl),
            'customer' => [
                'id' => Auth::id(),
            ],
        ]);

        if (! $response->successful()) {
            Log::warning("[Shop] Paysafecard - Invalid init response from {$response->effectiveUri()} : {$response->json()['message']}");

            return $this->errorResponse();
        }

        $this->createPayment($cart, $amount, $currency, $response['id']);

        return redirect()->away($response['redirect']['auth_url']);
    }

    public function notification(Request $request, ?string $paymentId)
    {
        abort_if($paymentId === null, 404);

        return response()->json($this->processPscPayment($paymentId));
    }

    public function success(Request $request)
    {
        $paymentId = $request->input('id');

        if ($paymentId === null) {
            return $this->errorResponse();
        }

        $payment = $this->processPscPayment($paymentId);

        if ($payment['status'] !== true) {
            Log::warning("[Shop] Paysafecard - {$payment['message']}");

            return $this->errorResponse();
        }

        return view('shop::payments.success');
    }

    private function processPscPayment(string $paymentId)
    {
        $payment = Payment::firstWhere('transaction_id', $paymentId);

        $response = $this->retrievePayment($paymentId);

        if (! $response->successful()) {
            return [
                'status' => false,
                'message' => "Invalid payment response from {$response->effectiveUri()}: {$response->json()['message']}",
            ];
        }

        $status = $response->json()['status'];

        if ($status === 'SUCCESS') {
            // Payment already successfully completed
            return ['status' => true];
        }

        if ($status !== 'AUTHORIZED') {
            return [
                'status' => false,
                'message' => "Invalid payment status: {$status}",
            ];
        }

        $response = $this->prepareRequest()
            ->post("{$paymentId}/capture", ['id' => $paymentId]);

        if (! $response->successful()) {
            return [
                'status' => false,
                'message' => "Invalid capture response: {$response->body()}",
            ];
        }

        $status = $response->json()['status'];

        if ($status !== 'SUCCESS') {
            return [
                'status' => false,
                'message' => "Invalid capture status: {$status}",
            ];
        }

        $payment->deliver();

        return ['status' => true];
    }

    public function view()
    {
        return 'shop::admin.gateways.methods.paysafecard';
    }

    public function rules()
    {
        return [
            'key' => ['required', 'string', 'starts_with:psc_'],
            'environment' => ['required', Rule::in(self::ENVIRONMENTS)],
        ];
    }

    private function prepareRequest()
    {
        $domain = $this->gateway->data['environment'] === 'production' ? 'api' : 'apitest';
        $url = "https://{$domain}.paysafecard.com/v1/payments";
        $token = base64_encode($this->gateway->data['key']);

        return Http::withToken($token, 'Basic')->baseUrl($url);
    }

    private function retrievePayment(string $paymentId)
    {
        return $this->prepareRequest()->get($paymentId);
    }

    public static function environments()
    {
        return self::ENVIRONMENTS;
    }
}
