<?php

namespace Azuriom\Plugin\Shop\Controllers\Api;

use Azuriom\Plugin\Shop\Models\Gateway;
use Illuminate\Http\Request;

class PaymentController
{
    /**
     * Handle a payment notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Azuriom\Plugin\Shop\Models\Gateway  $gateway
     * @param  string|null  $id
     * @return \Illuminate\Http\Response
     */
    public function notification(Request $request, Gateway $gateway, string $id = null)
    {
        return $gateway->paymentMethod()->notification($request, $id);
    }
}
