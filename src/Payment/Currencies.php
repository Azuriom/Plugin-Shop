<?php

namespace Azuriom\Plugin\Shop\Payment;

class Currencies
{
    private const CURRENCIES = [
        'USD' => 'US Dollar',
        'EUR' => 'Euro',
        'GBP' => 'Pound Sterling',
        'CAD' => 'Canadian Dollar',
        'AUD' => 'Australian Dollar',
        'JPY' => 'Japanese Yen',
        'ARS' => 'Argentine Peso',
        'BGN' => 'Bulgarian Lev',
        'BRL' => 'Brazilian Real',
        'CHF' => 'Swiss Franc',
        'CLP' => 'Chilean Peso',
        'CNY' => 'Chinese Yuan Renminbi',
        'CZK' => 'Czech Koruna',
        'DKK' => 'Danish Krone',
        'HKD' => 'Hong Kong Dollar',
        'HUF' => 'Hungarian Forint',
        'IDR' => 'Indonesian Rupiah',
        'ILS' => 'Israeli Sheqel',
        'INR' => 'Indian Rupee',
        'KRW' => 'South Korean Won',
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
        'ZAR' => 'South African Rand',
    ];

    private const SYMBOLS = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'JPY' => '¥',
        'BGN' => 'лв',
        'BRL' => 'R$',
        'CZK' => 'Kč',
        'DKK' => 'kr.',
        'HKD' => 'HK$',
        'HUF' => 'Ft',
        'IDR' => 'Rp',
        'ILS' => '₪',
        'INR' => '₹',
        'KRW' => '₩',
        'MYR' => 'RM',
        'PHP' => '₱',
        'PLN' => 'zł',
        'RON' => 'lei',
        'RUB' => '₽',
        'THB' => '฿',
        'TRY' => '₺',
        'TWD' => 'NT$',
        'UAH' => '₴',
        'ZAR' => 'R',
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

    public static function formatAmount(float $amount, string $currencyCode): string
    {
        $symbol = self::symbol($currencyCode);

        return match (strtoupper($currencyCode)) {
            'USD', 'GBP', 'JPY', 'BRL', 'DKK', 'HKD', 'IDR', 'INR', 'KRW',
            'MYR','PHP', 'THB', 'TRY', 'TWD', 'UAH', 'ZAR' => $symbol.$amount,
            'EUR' => $amount.$symbol,
            default => $amount.' '.$symbol,
        };
    }
}
