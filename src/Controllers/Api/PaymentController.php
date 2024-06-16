<?php

namespace Azuriom\Plugin\Shop\Controllers\Api;

use Azuriom\Plugin\Shop\Models\Gateway;
use Illuminate\Http\Request;

class PaymentController
{
    /**
     * Handle a payment notification.
     */
    public function notification(Request $request, Gateway $gateway, ?string $id = null)
    {
        return $gateway->paymentMethod()->notification($request, $id);
    }
}
