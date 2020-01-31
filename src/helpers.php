<?php

use Azuriom\Plugin\Shop\Payment\Currencies;
use Azuriom\Plugin\Shop\Payment\PaymentManager;

/*
|--------------------------------------------------------------------------
| Helper functions
|--------------------------------------------------------------------------
|
| Here is where you can register helpers for your plugin. These
| functions are loaded by Composer and are globally available on the app !
| Just make sure you verify that a function don't exists before registering it
| to prevent any side effect.
|
*/

if (! function_exists('payment_manager')) {
    /**
     * Get the payment manager of the shop.
     *
     * @return \Azuriom\Plugin\Shop\Payment\PaymentManager
     */
    function payment_manager()
    {
        return app(PaymentManager::class);
    }
}

if (! function_exists('use_site_money')) {
    function use_site_money()
    {
        return setting('shop.use-site-money', false);
    }
}

if (! function_exists('currency')) {
    function currency()
    {
        return setting('currency', 'USD');
    }
}

if (! function_exists('currency_display')) {
    function currency_display(string $currency = null)
    {
        return Currencies::symbol($currency ?? currency());
    }
}

if (! function_exists('shop_active_currency')) {
    function shop_active_currency(bool $plural = true)
    {
        return use_site_money() ? money_name($plural) : currency_display();
    }
}

if (! function_exists('shop_format_amount')) {
    function shop_format_amount(float $amount)
    {
        return $amount.' '.shop_active_currency($amount != 1);
    }
}
