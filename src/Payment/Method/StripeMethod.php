<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Cart\CartItem;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeMethod extends PaymentMethod
{
    public const PAYMENT_METHODS = [
        'acss_debit' => 'ACSS Debit',
        'afterpay_clearpay' => 'Afterpay / Clearpay',
        'alipay' => 'Alipay',
        'bacs_debit' => 'Bacs Direct Debit',
        'bancontact' => 'Bancontact',
        'boleto' => 'Boleto',
        'eps' => 'EPS',
        'fpx' => 'FPX',
        'giropay' => 'giropay',
        'grabpay' => 'GrabPay',
        'ideal' => 'iDEAL',
        'klarna' => 'Klarna',
        'oxxo' => 'OXXO',
        'p24' => 'Przelewy24',
        'sepa_debit' => 'SEPA Direct Debit',
        'sofort' => 'Sofort',
        'wechat_pay' => 'WeChat Pay',
    ];

    /**
     * The payment method id name.
     *
     * @var string
     */
    protected $id = 'stripe';

    /**
     * The payment method display name.
     *
     * @var string
     */
    protected $name = 'Stripe';

    public function startPayment(Cart $cart, float $amount, string $currency)
    {
        $user = auth()->user();
        $this->setup();

        $items = $cart->content()->map(function (CartItem $item) use ($currency) {
            return [
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount' => (int) ($item->price() * 100),
                    'product_data' => [
                        'name' => $item->name(),
                    ],
                ],
                'quantity' => $item->quantity,
            ];
        });

        $payment = $this->createPayment($cart, $amount, $currency);

        $successUrl = route('shop.payments.success', [$this->id, '%id%']);
        $methods = $this->gateway->data['methods'] ?? [];
        $params = [
            'payment_method_types' => array_merge($methods, ['card']),
            'mode' => 'payment',
            'customer_email' => $user->email,
            'line_items' => $items->all(),
            'success_url' => str_replace('%id%', '{CHECKOUT_SESSION_ID}', $successUrl),
            'cancel_url' => route('shop.cart.index'),
            'client_reference_id' => $payment->id,
        ];

        if (in_array('afterpay', $methods, true)) {
            $params['shipping_address_collection'] = [
                'allowed_countries' => ['AU', 'CA', 'GB', 'FR', 'NZ', 'UK', 'US'],
            ];
        }

        $session = Session::create($params);

        $payment->update(['transaction_id' => $session->id]);

        return redirect()->away($session->url);
    }

    public function notification(Request $request, ?string $paymentId)
    {
        $endpointSecret = $this->gateway->data['endpoint-secret'];
        $stripeSignature = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($request->getContent(), $stripeSignature, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type !== 'checkout.session.completed') {
            return response()->json(['status' => 'unknown']);
        }

        $payment = Payment::find($event->data->object->client_reference_id);

        return $this->processPayment($payment, $paymentId);
    }

    public function view()
    {
        return 'shop::admin.gateways.methods.stripe';
    }

    public function rules()
    {
        return [
            'secret-key' => ['required', 'string'],
            'public-key' => ['required', 'string'],
            'endpoint-secret' => ['nullable', 'string'],
            'methods.*' => [Rule::in(array_keys(static::PAYMENT_METHODS))],
        ];
    }

    private function setup()
    {
        Stripe::setLogger(logger()->driver());
        Stripe::setApiKey($this->gateway->data['secret-key']);
    }
}
