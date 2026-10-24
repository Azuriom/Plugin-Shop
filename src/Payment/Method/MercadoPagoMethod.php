<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Request;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoMethod extends PaymentMethod
{
    /**
     * The payment method id name.
     *
     * @var string
     */
    protected $id = 'mercadopago';

    /**
     * The payment method display name.
     *
     * @var string
     */
    protected $name = 'Mercado Pago';

    /**
     * Start the payment process for the given cart.
     *
     * @link https://www.mercadopago.com.ar/developers/en/docs/checkout-pro/integrate-preferences
     */
    public function startPayment(Cart $cart, float $amount, string $currency)
    {
        $this->setupConfig();
        $client = new PreferenceClient();
        $payment = $this->createPayment($cart, $amount, $currency);

        try {
            $preference = $client->create([
                'items' => [
                    [
                        'title' => $this->getPurchaseDescription($payment),
                        'quantity' => 1,
                        'unit_price' => $amount,
                        'currency_id' => $currency,
                    ],
                ],
                'payer' => [
                    'email' => $this->getUserEmail($payment->user),
                ],
                'back_urls' => [
                    'success' => route('shop.payments.success', $this->id),
                    'failure' => route('shop.payments.failure', $this->id),
                    'pending' => route('shop.home'),
                ],
                'auto_return' => 'approved',
                'notification_url' => route('shop.payments.notification', $this->id),
                'external_reference' => $payment->id,
            ]);

            return redirect()->away($preference->init_point);
        } catch (MPApiException $e) {
            logger()->error('[Shop] Unable to start Mercado Pago payment', $e->getApiResponse()->getContent());

            return to_route('shop.cart.index')->with('error', trans('shop::messages.payment.error'));
        }
    }

    /**
     * Handle the payment notification webhook.
     *
     * @link https://www.mercadopago.com.ar/developers/en/docs/your-integrations/notifications/webhooks
     */
    public function notification(Request $request, ?string $paymentId)
    {
        $this->setupConfig();
        // PHP normalizes dots in query-string keys to underscores
        $id = $request->query('data_id') ?? $request->input('data.id');

        try {
            if ($id === null || $request->input('type') !== 'payment') {
                return response()->noContent();
            }

            if (! $this->verifySignature($request, (string) $id)) {
                return response()->json(['error' => 'Invalid webhook signature.'], 401);
            }

            $mercadoPayment = (new PaymentClient())->get($id);
            $payment = Payment::find($mercadoPayment->external_reference);

            if ($payment === null || $payment->gateway_type !== $this->id) {
                logger()->warning('[Shop] Payment not found for Mercado Pago payment '.$id);

                return response()->json(['error' => 'Payment not found'], 404);
            }

            // Individual failed attempts will not necessarily end Checkout Pro preferences,
            // and mediations are like inquiries: a refund or chargeback may follow.
            if (in_array($mercadoPayment->status, ['cancelled', 'pending', 'authorized', 'in_process', 'in_mediation', 'rejected'], true)) {
                return response()->json(['status' => 'ok']);
            }

            if ($mercadoPayment->status === 'charged_back') {
                return $this->processChargeback($payment);
            }

            if ($mercadoPayment->status === 'refunded') {
                return $this->processRefund($payment);
            }

            if ($mercadoPayment->status !== 'approved') {
                logger()->warning("[Shop] Invalid Mercado Pago payment status for payment {$id}: {$mercadoPayment->status}");

                return response()->json([
                    'error' => 'Invalid payment status: '.$mercadoPayment->status,
                ], 400);
            }

            return $this->processPayment($payment, $mercadoPayment->id);
        } catch (MPApiException $e) {
            logger()->error('[Shop] Mercado Pago notification failed', $e->getApiResponse()->getContent());

            return response()->json(['error' => 'Internal error'], 500);
        }
    }

    public function view(): string
    {
        return 'shop::admin.gateways.methods.mercadopago';
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string'],
            'webhook-secret' => ['required', 'string'],
        ];
    }

    private function verifySignature(Request $request, string $paymentId): bool
    {
        $signature = $request->header('X-Signature');
        $requestId = $request->header('X-Request-ID');

        if (! is_string($signature) || ! is_string($requestId) || $requestId === '') {
            return false;
        }

        $secret = $this->gateway->data['webhook-secret'] ?? null;

        if ($secret === null) {
            return true; // For backwards compatibility
        }

        $parts = [];
        foreach (explode(',', $signature) as $part) {
            $pair = explode('=', trim($part), 2);
            if (count($pair) === 2) {
                $parts[$pair[0]] = $pair[1];
            }
        }

        if (empty($parts['ts']) || empty($parts['v1'])) {
            return false;
        }

        // Mercado Pago signs this manifest, using lowercase data.id when alphanumeric.
        $manifest = 'id:'.strtolower($paymentId).';request-id:'.$requestId.';ts:'.$parts['ts'].';';
        $expected = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expected, $parts['v1']);
    }

    private function setupConfig(): void
    {
        MercadoPagoConfig::setAccessToken($this->gateway->data['key']);
    }
}
