<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Request;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class PayPalExpressCheckout extends PaymentMethod
{
    /**
     * The payment method id name.
     *
     * @var string
     */
    protected $id = 'paypal-express-checkout';

    /**
     * The payment method display name.
     *
     * @var string
     */
    protected $name = 'PayPal Express Checkout';

    /**
     * The payment method image.
     *
     * @var string
     */
    protected $image = 'paypal-express-checkout.png';

    public function startPayment(Cart $cart, float $total, string $currency)
    {
        $payment = $this->createPayment($cart, $total, $currency);

        $items = [];

        foreach ($cart->content() as $cartItem) {
            $item = [
            	'name' => $cartItem->name(),
            	'description' => $cartItem->buyable()->getDescription(),
            	'sku' => $cartItem->id,
            	'unit_amount' => [
            		'currency_code' => $currency,
            		'value' => $cartItem->buyable()->getPrice(),
            	],
            	'quantity' => $cartItem->quantity,
            	'category' => 'DIGITAL_GOODS',
            ];

            $items[] = $item;
        }

        $request = new OrdersCreateRequest();
        $request->headers['prefer'] = 'return=representation';
        $request->body = [
        	'intent' => 'CAPTURE',
        	'application_context' => [
        		'return_url' => route('shop.payments.success', $this->id),
        		'cancel_url' => route('shop.cart.index'),
        		'brand_name' => 'Azuriom',
        		'locale' => str_replace('_', '-', app()->getLocale()),
        		'landing_page' => 'BILLING',
        		'user_action' => 'PAY_NOW',
        	],
        	'purchase_units' => [
        		[
        			'description' => $this->getPurchaseDescription($payment->id),
        			'custom_id' => $payment->id,
        			'soft_descriptor' => $payment->id,
        			'amount' => [
        				'currency_code' => $currency,
        				'value' => $total,
        				'breakdown' => [
        					'item_total' => [
        						'currency_code' => $currency,
        						'value' => $total,
        					],
        				]
        			],
        			'items' => $items,
        		],
        	],
        ];

        $response = $this->getClient()->execute($request);

        if ($response->statusCode != 201) {
        	logger()->warning('Error occured while creating order with PayPal.');

        	abort(500);
        }

        $payment->update(['transaction_id' => $response->result->id]);

        $approveLink = null;
        foreach ($response->result->links as $link) {
        	if ($link->rel === 'approve') {
        		$approveLink = $link->href;
        		break;
        	}
        }

        return redirect()->away($approveLink);
    }

    public function notification(Request $request, ?string $paymentId)
    {
        abort(404);
    }

    public function success(Request $request)
    {
        $environment = $this->getEnvironment();
        $token = $request->input('token');

        $payment = Payment::firstWhere('transaction_id', $token);

        if ($payment === null) {
            logger()->warning('Invalid order id: '.$token);

            return $this->errorResponse();
        }

        if ($payment->isPending()) {
	        $request = new OrdersCaptureRequest($token);

	        $response = $this->getClient()->execute($request);
            
            if ($response->statusCode != 201) {
            	logger()->warning('Invalid response while capturing order '.$token);

            	return $this->errorResponse();
            }

            $payment->update(['transaction_id' => $response->result->purchase_units[0]->payments->captures[0]->id]);

            $payment->deliver();

            return view('shop::payments.success');
        }

        if (! $payment->isCompleted()) {
            logger()->warning('Invalid payment status for '.$token);

            return $this->errorResponse();
        }

        return view('shop::payments.success');
    }

    public function view()
    {
        return 'shop::admin.gateways.methods.paypal-express-checkout';
    }

    public function rules()
    {
        return [
            'client-id' => ['required', 'string'],
            'secret' => ['required', 'string'],
        ];
    }

    private function getClient()
    {
        return new PayPalHttpClient($this->getEnvironment());
    }

    private function getEnvironment()
    {
        $id = $this->gateway->data['client-id'];
        $secret = $this->gateway->data['secret'];

        return new ProductionEnvironment($id, $secret);
    }
}
