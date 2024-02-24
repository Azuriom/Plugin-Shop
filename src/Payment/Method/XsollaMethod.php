<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Request;
use Xsolla\SDK\API\PaymentUI\TokenRequest;
use Xsolla\SDK\API\XsollaClient;
use Xsolla\SDK\Exception\Webhook\InvalidInvoiceException;
use Xsolla\SDK\Exception\Webhook\InvalidUserException;
use Xsolla\SDK\Exception\Webhook\XsollaWebhookException;
use Xsolla\SDK\Webhook\Message\Message;
use Xsolla\SDK\Webhook\WebhookRequest;
use Xsolla\SDK\Webhook\WebhookServer;

class XsollaMethod extends PaymentMethod
{
    /**
     * The payment method id name.
     *
     * @var string
     */
    protected $id = 'xsolla';

    /**
     * The payment method display name.
     *
     * @var string
     */
    protected $name = 'Xsolla';

    public function startPayment(Cart $cart, float $amount, string $currency)
    {
        $sandbox = ($this->gateway->data['sandbox'] === 'true');
        $projectId = $this->gateway->data['project-id'];
        $payment = $this->createPayment($cart, $amount, $currency);

        // Their PHP SDK need casts everywhere...
        $tokenRequest = new TokenRequest((int) $projectId, (string) $payment->user->id);
        $tokenRequest->setUserEmail($payment->user->mail)
            ->setUserName($payment->user->name)
            ->setExternalPaymentId((string) $payment->id)
            ->setSandboxMode($sandbox)
            ->setPurchase($amount, $currency);

        $xsollaClient = XsollaClient::factory([
            'merchant_id' => $this->gateway->data['merchant-id'],
            'api_key' => $this->gateway->data['api-key'],
        ]);

        $token = $xsollaClient->createPaymentUITokenFromRequest($tokenRequest);

        $domain = ($sandbox ? 'sandbox-' : '').'secure.xsolla.com';

        return redirect()->away("https://{$domain}/paystation2/?access_token={$token}");
    }

    public function notification(Request $request, ?string $paymentId)
    {
        $webhookServer = WebhookServer::create(function (Message $message) {
            if ($message->isUserValidation()) {
                $this->handleUserValidation($message);

                return;
            }

            $this->handleMessage($message);
        }, $this->gateway->data['secret-key']);

        $headers = array_map(function ($headers) {
            return $headers[0];
        }, $request->headers->all());

        $webhookRequest = new WebhookRequest($headers, $request->getContent(), $request->ip());

        return $webhookServer->getSymfonyResponse($webhookRequest);
    }

    public function view(): string
    {
        return 'shop::admin.gateways.methods.xsolla';
    }

    public function rules(): array
    {
        return [
            'merchant-id' => ['required', 'string'],
            'api-key' => ['required', 'string'],
            'project-id' => ['required', 'string'],
            'secret-key' => ['required', 'string'],
            'sandbox' => ['required', 'in:true,false'],
        ];
    }

    protected function handleMessage(Message $message): void
    {
        if (! $message->isPayment() && ! $message->isRefund()) {
            throw new XsollaWebhookException('Notification type not implemented');
        }

        /** @var \Xsolla\SDK\Webhook\Message\PaymentMessage $message */
        $payment = Payment::find($message->getExternalPaymentId());

        if ($payment === null) {
            throw new InvalidInvoiceException('Unknown payment id: '.$message->getExternalPaymentId());
        }

        if ($message->isRefund()) {
            $this->processRefund($payment);

            return;
        }

        $this->processPayment($payment, $message->getPaymentId());
    }

    protected function handleUserValidation(Message $message): void
    {
        $userId = $message->getUserId();

        if (! is_numeric($userId) || ! User::whereKey($userId)->exists()) {
            throw new InvalidUserException('Unknown user id: '.$message->getUserId());
        }
    }
}

namespace GuzzleHttp\Psr7;

// Workaround for https://github.com/xsolla/xsolla-sdk-php/issues/95
if (! function_exists('GuzzleHttp\Psr7\str')) {
    function str($message)
    {
        return Message::toString($message);
    }
}
