<?php

use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Payment\Currencies;
use Azuriom\Plugin\Shop\Payment\PaymentManager;
use Illuminate\Support\Facades\Request;

/*
|--------------------------------------------------------------------------
| Helper functions
|--------------------------------------------------------------------------
|
| Here is where you can register helpers for your plugin. These
| functions are loaded by Composer and are globally available on the app !
| Just make sure you verify that a function doesn't exist before registering it
| to prevent any side effect.
|
*/

if (! function_exists('payment_manager')) {
    /**
     * Get the payment manager of the shop.
     */
    function payment_manager(): PaymentManager
    {
        return app(PaymentManager::class);
    }
}

if (! function_exists('use_site_money')) {
    /**
     * Return whether the site money should be used for purchases.
     */
    function use_site_money(): bool
    {
        return setting('shop.use_site_money', false);
    }
}

if (! function_exists('currency')) {
    /**
     * Return the active currency.
     */
    function currency(): string
    {
        return strtoupper(setting('currency', 'USD'));
    }
}

if (! function_exists('currency_display')) {
    /**
     * Return the display of the given currency.
     */
    function currency_display(?string $currency = null): string
    {
        return Currencies::symbol($currency ?? currency());
    }
}

if (! function_exists('shop_active_currency')) {
    /**
     * Return active shop currency or the site money.
     */
    function shop_active_currency(float $amount = 2): string
    {
        return use_site_money() ? money_name($amount) : currency_display();
    }
}

if (! function_exists('shop_format_amount')) {
    /**
     * Format the given amount with the active currency or the site money.
     */
    function shop_format_amount(float $amount): string
    {
        return $amount.' '.shop_active_currency($amount);
    }
}

if (! function_exists('shop_cart')) {
    /**
     * Get the cart of the current user, or create a new one.
     */
    function shop_cart(): Cart
    {
        return Cart::fromSession(Request::session());
    }
}
