<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Request;
use Paymentwall_Config as PaymentWallConfig;
use Paymentwall_Pingback as Pingback;
use Paymentwall_Product as Product;
use Paymentwall_Widget as Widget;

class PaymentWallMethod extends PaymentMethod
{
    /**
     * The payment method id name.
     *
     * @var string
     */
    protected $id = 'paymentwall';

    /**
     * The payment method display name.
     *
     * @var string
     */
    protected $name = 'PaymentWall';

    public function startPayment(Cart $cart, float $amount, string $currency)
    {
        $this->setupConfig();

        $user = auth()->user();

        $payment = $this->createPayment($cart, $amount, $currency);

        $widget = new Widget(
            $user->id,
            'p1_1',
            [
                new Product(
                    'payment_'.$payment->id,
                    $amount,
                    $currency,
                    $this->getPurchaseDescription($payment->id),
                    Product::TYPE_FIXED
                ),
            ],
            [
                'email' => $user->email,
                'customer[username]' => $user->name,
                'history[registration_date]' => $user->created_at->timestamp,
                'success_url' => route('shop.payments.success', $this->id),
            ]
        );

        return redirect()->away($widget->getUrl());
    }

    public function notification(Request $request, ?string $paymentId)
    {
        $this->setupConfig();

        $pingback = new Pingback($request->all(), $request->ip());

        if (! $pingback->validate()) {
            return response()->json(['status' => false, 'message' => 'Payment not validated']);
        }

        if (! $pingback->isDeliverable()) {
            return response()->json(['status' => false, 'message' => 'Payment not deliverable']);
        }

        $payment = Payment::find(str_replace('payment_', '', $pingback->getProduct()->getId()));

        return $this->processPayment($payment, $pingback->getReferenceId());
    }

    public function success(Request $request)
    {
        return view('shop::payments.success');
    }

    public function view()
    {
        return 'shop::admin.gateways.methods.paymentwall';
    }

    public function rules()
    {
        return [
            'private-key' => ['required', 'string'],
            'public-key' => ['required', 'string'],
        ];
    }

    private function setupConfig()
    {
        PaymentWallConfig::getInstance()->set([
            'api_type' => PaymentWallConfig::API_GOODS,
            'public_key' => $this->gateway->data['public-key'],
            'private_key' => $this->gateway->data['private-key'],
        ]);
    }
}
