<?php

namespace Azuriom\Plugin\Shop\Events;

use Azuriom\Plugin\Shop\Models\Payment;
use Illuminate\Queue\SerializesModels;

class PaymentPaid
{
    use SerializesModels;

    public Payment $payment;

    /**
     * Create a new event instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }
}
