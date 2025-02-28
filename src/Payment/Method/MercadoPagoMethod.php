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
                    'email' => $payment->user->email,
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

            return redirect()->route('shop.cart.index')->with('error', trans('shop::messages.payment.error'));
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
        $id = $request->input('data.id');

        try {
            if (! $request->hasHeader('X-Request-ID') || ! $request->hasHeader('X-Signature')) {
                return response()->json(['error' => 'Invalid webhook signature.'], 400);
            }

            if ($id === null || $request->input('type') !== 'payment') {
                return response()->noContent();
            }

            $mercadoPayment = (new PaymentClient())->get($id);
            $payment = Payment::findOrFail($mercadoPayment->external_reference);

            if ($payment === null) {
                logger()->warning('[Shop] Payment not found for Mercado Pago payment '.$id);

                return response()->json(['error' => 'Payment not found'], 404);
            }

            if (in_array($mercadoPayment->status, ['cancelled', 'pending', 'in_process'])) {
                return response()->json(['status' => 'ok']);
            }

            if ($mercadoPayment->status === 'refunded') {
                return $this->processRefund($payment);
            }

            if ($mercadoPayment->status === 'rejected') {
                return $this->invalidPayment($payment, $mercadoPayment->id, 'Rejected payment');
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
        ];
    }

    private function setupConfig(): void
    {
        MercadoPagoConfig::setAccessToken($this->gateway->data['key']);
    }
}
