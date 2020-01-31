<?php

namespace Azuriom\Plugin\Shop\Controllers\Api;

use Azuriom\Plugin\Shop\Models\Gateway;
use Illuminate\Http\Request;

class PaymentController
{
    /**
     * Handle a payment notification.
     *
     * @param  Request  $request
     * @param  string  $gateway
     * @param  string|null  $id
     * @return \Illuminate\Http\Response
     */
    public function notification(Request $request, string $gateway, string $id = null)
    {
        $gateway = Gateway::where('type', $gateway)->firstOrFail();

        return $gateway->paymentMethod()->notification($request, $id);
    }
}
