<?php

namespace Azuriom\Plugin\Shop\Payment;

class Currencies
{
    private const CURRENCIES = [
        'USD' => 'US Dollar',
        'EUR' => 'Euro',
        'GBP' => 'Pound Sterling',
        'JPY' => 'Japanese Yen',
        'AUD' => 'Australian Dollar',
        'ARS' => 'Argentine Peso',
        'BRL' => 'Brazilian Real',
        'CAD' => 'Canadian Dollar',
        'CLP' => 'Chilean Peso',
        'CNY' => 'Yuan Renminbi',
        'CZK' => 'Czech Koruna',
        'DKK' => 'Danish Krone',
        'HKD' => 'Hong Kong Dollar',
        'HUF' => 'Hungarian Forint',
        'INR' => 'Indian Rupee',
        'IDR' => 'Indonesian Rupiah',
        'ILS' => 'New Israeli Sheqel',
        'MYR' => 'Malaysian Ringgit',
        'MXN' => 'Mexican Peso',
        'TWD' => 'New Taiwan Dollar',
        'NZD' => 'New Zealand Dollar',
        'NOK' => 'Norwegian Krone',
        'PHP' => 'Philippine Peso',
        'PLN' => 'Polish Zloty',
        'RUB' => 'Russian Ruble',
        'SGD' => 'Singapore Dollar',
        'SEK' => 'Swedish Krona',
        'CHF' => 'Swiss Franc',
        'THB' => 'Thai Baht',
        'UAH' => 'Ukrainian Hryvnia',
        'UYU' => 'Uruguayan Peso',
        'ZAR' => 'South African rand',
    ];

    private const SYMBOLS = [
        'EUR' => '€',
        'GBP' => '£',
        'JPY' => '¥',
        'USD' => '$',
    ];

    public static function all()
    {
        return self::CURRENCIES;
    }

    public static function codes()
    {
        return array_keys(self::CURRENCIES);
    }

    public static function symbols()
    {
        return self::SYMBOLS;
    }

    public static function symbol(string $currencyCode)
    {
        return self::SYMBOLS[$currencyCode] ?? $currencyCode;
    }
}
