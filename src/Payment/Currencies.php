<?php

namespace Azuriom\Plugin\Shop\Payment;

class Currencies
{
    private const CURRENCIES = [
        'USD' => 'US Dollar',
        'EUR' => 'Euro',
        'GBP' => 'Pound Sterling',
        'JPY' => 'Japanese Yen',
        'ARS' => 'Argentine Peso',
        'AUD' => 'Australian Dollar',
        'BRL' => 'Brazilian Real',
        'CAD' => 'Canadian Dollar',
        'CHF' => 'Swiss Franc',
        'CLP' => 'Chilean Peso',
        'CNY' => 'Yuan Renminbi',
        'CZK' => 'Czech Koruna',
        'DKK' => 'Danish Krone',
        'HKD' => 'Hong Kong Dollar',
        'HUF' => 'Hungarian Forint',
        'IDR' => 'Indonesian Rupiah',
        'ILS' => 'New Israeli Sheqel',
        'INR' => 'Indian Rupee',
        'MXN' => 'Mexican Peso',
        'MYR' => 'Malaysian Ringgit',
        'NOK' => 'Norwegian Krone',
        'NZD' => 'New Zealand Dollar',
        'PHP' => 'Philippine Peso',
        'PLN' => 'Polish Zloty',
        'RON' => 'Romanian Leu',
        'RUB' => 'Russian Ruble',
        'SEK' => 'Swedish Krona',
        'SGD' => 'Singapore Dollar',
        'THB' => 'Thai Baht',
        'TRY' => 'Turkish Lira',
        'TWD' => 'New Taiwan Dollar',
        'UAH' => 'Ukrainian Hryvnia',
        'UYU' => 'Uruguayan Peso',
        'ZAR' => 'South African rand',
    ];

    private const SYMBOLS = [
        'EUR' => '€',
        'GBP' => '£',
        'JPY' => '¥',
        'USD' => '$',
        'BRL' => 'R$',
        'CZK' => 'Kč',
        'HUF' => 'Ft',
        'IDR' => 'Rp',
        'ILS' => '₪',
        'INR' => '₹',
        'PHP' => '₱',
        'PLN' => 'zł',
        'RUB' => '₽',
        'SGD' => 'S$',
        'THB' => '฿',
        'TRY' => '₺',
        'TWD' => 'NT$',
        'UAH' => '₴',
    ];

    public static function all(): array
    {
        return self::CURRENCIES;
    }

    public static function codes(): array
    {
        return array_keys(self::CURRENCIES);
    }

    public static function symbols(): array
    {
        return self::SYMBOLS;
    }

    public static function symbol(string $currencyCode): string
    {
        return self::SYMBOLS[strtoupper($currencyCode)] ?? $currencyCode;
    }
}
