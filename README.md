# Shop (Plugin)

[![Style](https://github.styleci.io/repos/237491356/shield)](https://github.styleci.io/repos/237491356)
[![Chat](https://img.shields.io/discord/625774284823986183?color=5865f2&label=Discord&logo=discord&logoColor=fff&style=flat-square)](https://azuriom.com/discord)

A shop plugin to sell in-game items on your website.

## Supported payment gateways

* [PayPal](https://www.paypal.com/)
* [PayPal Checkout](https://www.paypal.com/) (supports subscriptions)
* [Mollie](https://www.mollie.com/) (supports subscriptions)
* [Xsolla](https://xsolla.com/)
* [Skrill](https://www.skrill.com/) (ex paysafecard)
* [Stripe](https://stripe.com/) (supports subscriptions)
* [PaymentWall](https://www.paymentwall.com/)

## Custom payment gateway

> [!NOTE]
> Due to the large number of different payment methods available, we won't be adding any directly to the shop plugin.
> However, new payment methods can be added via a plugin, as explained below.
> It is also possible to post the plugin on the [market](https://market.azuriom.com/) to make the payment method easily accessible for users.

You can create your own payment gateway by creating a new class that extends the `Azuriom\Plugin\Shop\Payment\PaymentMethod\PaymentMethod` class.

The `$id` and `$name` properties are required to be set in the class (the ID is the unique identifier of the payment gateway and should be lowercase).

```php
<?php

namespace Azuriom\Plugin\BestPayment;

use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Request;

class BestPaymentMethod extends PaymentMethod
{
    /**
     * The payment method id name.
     *
     * @var string
     */
    protected $id = 'best-payment';

    /**
     * The payment method display name.
     *
     * @var string
     */
    protected $name = 'Best Payment';

    /**
     * Start a new payment with this method and return the payment response to the user (redirect, form, ...).
     */
    public function startPayment(Cart $cart, float $amount, string $currency)
    {
        // Create a new pending payment with the cart items
        $payment = $this->createPayment($cart, $amount, $currency);
        
        // Start the payment process with the payment gateway
        $response = Http::post('https://api.bestpayment.pay', [            
            // The routes below will automatically call the methods in this class
            'success_url' => route('shop.payments.success', $this->id),
            'failure_url' => route('shop.payments.failure', $this->id),
            'status_url' => route('shop.payments.notification', $this->id),
            'custom_id' => $payment->id, // the Azuriom payment identifier
            'amount' => $amount, // amount to pay
            'currency' => $currency, // ISO 4217 currency code
            'secret_key' => $this->gateway->data['secret_key'],
        ]);

        // Redirect the user to the payment gateway
        // You can also return a view depending on the payment gateway requirements
        return redirect()->away($response->json('url'));
    }

    /**
     * Handle a payment notification request sent by the payment gateway and return a response.
     */
    public function notification(Request $request, ?string $paymentId)
    {
        // This method is associated to `route('shop.payments.notification', $this->id)`

        // Always verify the request is authentic to avoid fraud
        abort_if(! $this->verifySignature($request), 400);

        $payment = Payment::findOrFail($request->input('custom_id'));
        $transactionId = $request->input('transaction_id');
        $status = $request->integer('status');

        if ($status === 'refunded') {
            return $this->processRefund($payment);
        }

        if ($status === 'chargeback') {
            return $this->processChargeback($payment);
        }

        if ($status !== 'success') {
            // You can return a response to the payment gateway to notify it of the error
            return $this->invalidPayment($payment, $transactionId, 'Invalid status: '.$status);
        }

        // Process the payment and deliver the items to the user
        return $this->processPayment($payment, $transactionId);
    }

    /**
     * Get the view for the gateway config in the admin panel.
     */
    public function view(): string
    {
        return 'best-payment::admin.config';
    }

    /**
     * Get the validation rules for the gateway config in the admin panel.
     */
    public function rules(): array
    {
        return [
            'public_key' => ['required', 'string'],
            'secret_key' => ['required', 'string'],
        ];
    }

    public function image(): string
    {
        return asset('plugins/best-payment/img/best.svg');
    }
}
```

Then, you need to register your payment gateway in the `boot()` method of your plugin service provider:

```php
public function boot(): void
{
    payment_manager()->registerPaymentMethod('best-payment', BestPaymentMethod::class);
}
```

Finally, the shop must be added in the `dependencies` of your `plugin.json` file:

```json5
{
  // ...
  "dependencies": {
    "shop": "^1.1.0"
  }
}
```

For a full example, you can check the [Dedipass Payment plugin](https://github.com/Azuriom/Plugin-DedipassPayment/).
