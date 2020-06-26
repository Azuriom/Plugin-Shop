<?php

namespace Azuriom\Plugin\Shop\Payment;

class Currencies
{
    private const CURRENCIES = [
        'USD' => 'United States dollar',
        'EUR' => 'Euro',
        'GBP' => 'Pound sterling',
        'JPY' => 'Japanese yen',
        'AUD' => 'Australian dollar',
        'BRL' => 'Brazilian real',
        'CAD' => 'Canadian dollar',
        'CZK' => 'Czech koruna',
        'DKK' => 'Danish krone',
        'HKD' => 'Hong Kong dollar',
        'HUF' => 'Hungarian forint',
        'INR' => 'Indian rupee',
        'ILS' => 'Israeli new shekel',
        'MYR' => 'Malaysian ringgit',
        'MXN' => 'Mexican peso',
        'TWD' => 'New Taiwan dollar',
        'NZD' => 'New Zealand dollar',
        'NOK' => 'Norwegian krone',
        'PHP' => 'Philippine peso',
        'PLN' => 'Polish Zloty',
        'RUB' => 'Russian ruble',
        'SGD' => 'Singapore dollar',
        'SEK' => 'Swedish krona',
        'CHF' => 'Swiss franc',
        'THB' => 'Thai baht',
    ];

    private const SYMBOLS = [
        'EUR' => '€',
        'GBP' => '£',
        'JPY' => '¥',
        'AUD' => '$',
        'CAD' => '$',
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
