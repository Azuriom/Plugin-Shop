<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Request;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment as PayPalPayment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

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

        $items = new ItemList();

        foreach ($cart->content() as $cartItem) {
            $item = new Item();
            $item->setName($cartItem->name())
                ->setDescription($cartItem->buyable()->getDescription())
                ->setQuantity($cartItem->quantity)
                ->setPrice($cartItem->buyable()->getPrice());

            $items->addItem($item);
        }

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(route('shop.payments.success', $this->id))
            ->setCancelUrl(route('shop.cart.index'));

        $amount = new Amount();
        $amount->setTotal($total)->setCurrency($currency);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setDescription($this->getPurchaseDescription($payment->id))
            ->setCustom($payment->id);

        $paypalPayment = new PayPalPayment();
        $paypalPayment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions([$transaction])
            ->setRedirectUrls($redirectUrls)
            ->create($this->getApiContext());

        $payment->update(['transaction_id' => $paypalPayment->getId()]);

        return redirect()->away($paypalPayment->getApprovalLink());
    }

    public function notification(Request $request, ?string $paymentId)
    {
        abort(404);
    }

    public function success(Request $request)
    {
        $apiContext = $this->getApiContext();
        $paymentId = $request->input('paymentId');

        $paypalPayment = PayPalPayment::get($paymentId, $apiContext);

        $payment = Payment::firstWhere('transaction_id', $paypalPayment->getId());

        if ($payment === null) {
            logger()->warning('Invalid payment id: '.$paymentId);

            return $this->errorResponse();
        }

        if ($payment->isPending()) {
            $execution = new PaymentExecution();
            $execution->setPayerId($request->input('PayerID'));

            $paypalPayment->execute($execution, $apiContext);

            $payment->deliver();

            return view('shop::payments.success');
        }

        if (! $payment->isCompleted()) {
            logger()->warning('Invalid payment status for '.$paymentId);

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

    private function getApiContext()
    {
        $id = $this->gateway->data['client-id'];
        $secret = $this->gateway->data['secret'];

        $apiContext = new ApiContext(new OAuthTokenCredential($id, $secret));

        return tap($apiContext)->setConfig(['mode' => 'live']);
    }
}
