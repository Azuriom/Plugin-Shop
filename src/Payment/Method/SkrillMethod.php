<?php

namespace Azuriom\Plugin\Shop\Payment\Method;

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SkrillMethod extends PaymentMethod
{
    /**
     * The supported languages by Skrill payment method.
     *
     * @see https://www.skrill.com/fileadmin/content/pdf/Skrill_Quick_Checkout_Guide_v10.3.pdf
     */
    private const LANGUAGES = [
        'BG', 'CS', 'DA', 'DE', 'EL', 'EN', 'ES', 'FI', 'FR', 'IT',
        'JA', 'KO', 'NL', 'PL', 'PT', 'RO', 'RU', 'SV', 'TR', 'ZH',
    ];

    private const STATUS_PROCESSED = 2;

    private const STATUS_FAILED = -2;

    private const STATUS_PENDING = 0;

    private const STATUS_CANCELED = -1;

    private const STATUS_CHARGEBACK = -3;

    /**
     * The payment method id name.
     *
     * @var string
     */
    protected $id = 'skrill';

    /**
     * The payment method display name.
     *
     * @var string
     */
    protected $name = 'Skrill (paysafecard)';

    public function startPayment(Cart $cart, float $amount, string $currency)
    {
        $user = auth()->user();
        $payment = $this->createPayment($cart, $amount, $currency);
        $locale = strtoupper(Str::before(app()->getLocale(), '_'));

        $response = Http::asForm()->post('https://pay.skrill.com', [
            'pay_to_email' => $this->gateway->data['email'],
            'recipient_description' => site_name(),
            'transaction_id' => $payment->id,
            'return_url' => route('shop.payments.success', $this->id),
            'cancel_url' => route('shop.payments.failure', $this->id),
            'status_url' => route('shop.payments.notification', [$this->id, $payment->id]),
            'language' => in_array($locale, self::LANGUAGES, true) ? $locale : 'EN',
            'prepare_only' => 1,
            'website_id' => $this->gateway->data['website_id'],
            'merchant_client_id' => $user->id,
            'merchant_client_registration_date' => $user->created_at->toDateString(),
            'amount' => $amount,
            'currency' => $currency,
        ]);

        $sessionId = $response->throw()->body();

        return redirect()->away('https://pay.skrill.com/?sid='.$sessionId);
    }

    public function notification(Request $request, ?string $paymentId)
    {
        abort_if(! $this->verifySignature($request), 403);

        $payment = Payment::findOrFail($request->input('transaction_id'));
        $transactionId = $request->input('mb_transaction_id');
        $status = $request->integer('status');
        $amount = $request->float('amount');
        $currency = $request->input('currency');

        if ($currency !== $payment->currency || $amount < $payment->price) {
            Log::warning("[Shop] Skrill - Invalid amount or currency for payment {$payment->id}");

            return $this->invalidPayment($payment, $transactionId, 'Invalid amount or currency');
        }

        if ($status === self::STATUS_CHARGEBACK) {
            return $this->processChargeback($payment);
        }

        if ($status !== self::STATUS_PROCESSED) {
            return $this->invalidPayment($payment, $transactionId, 'Invalid status: '.$status);
        }

        return $this->processPayment($payment, $transactionId);
    }

    protected function verifySignature(Request $request): bool
    {
        $signature = implode('', [
            $request->input('merchant_id'),
            $request->input('transaction_id'),
            strtoupper(md5($this->gateway->data['secret'])),
            $request->input('mb_amount'),
            $request->input('mb_currency'),
            $request->input('status'),
        ]);

        return strtoupper(md5($signature)) === $request->input('md5sig');
    }

    public function view(): string
    {
        return 'shop::admin.gateways.methods.skrill';
    }

    public function rules(): array
    {
        return [
            'website_id' => ['required', 'string'],
            'email' => ['required', 'email'],
            'secret' => ['required', 'string'],
        ];
    }
}
