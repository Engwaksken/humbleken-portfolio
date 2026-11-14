<?php

namespace App\Support;

/**
 * The site currency chosen in admin Settings, Payments. Uganda Shillings by default.
 */
class Currency
{
    public const DEFAULT = 'UGX';

    /** @var array<string, array{label: string, symbol: string, decimals: int}> */
    public const CURRENCIES = [
        'UGX' => ['label' => 'Ugandan Shilling (UGX)', 'symbol' => 'UGX', 'decimals' => 0],
        'KES' => ['label' => 'Kenyan Shilling (KES)', 'symbol' => 'KES', 'decimals' => 0],
        'TZS' => ['label' => 'Tanzanian Shilling (TZS)', 'symbol' => 'TZS', 'decimals' => 0],
        'RWF' => ['label' => 'Rwandan Franc (RWF)', 'symbol' => 'RWF', 'decimals' => 0],
        'USD' => ['label' => 'US Dollar (USD)', 'symbol' => '$', 'decimals' => 2],
        'EUR' => ['label' => 'Euro (EUR)', 'symbol' => '€', 'decimals' => 2],
        'GBP' => ['label' => 'British Pound (GBP)', 'symbol' => '£', 'decimals' => 2],
    ];

    public static function code(): string
    {
        $code = rescue(fn () => Settings::get('currency'), null, report: false);

        return is_string($code) && array_key_exists($code, self::CURRENCIES) ? $code : self::DEFAULT;
    }

    public static function symbol(): string
    {
        return self::CURRENCIES[static::code()]['symbol'];
    }

    public static function decimals(): int
    {
        return self::CURRENCIES[static::code()]['decimals'];
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return array_map(fn (array $currency): string => $currency['label'], self::CURRENCIES);
    }

    /**
     * Format an amount in the site currency, e.g. "UGX 150,000" or "$1,250.00".
     */
    public static function format(int|float|string|null $amount): ?string
    {
        if ($amount === null || $amount === '' || ! is_numeric($amount)) {
            return null;
        }

        $symbol = static::symbol();
        $number = number_format((float) $amount, static::decimals());

        // Letter codes read better with a space; signs sit directly before the number.
        return mb_strlen($symbol) > 1 ? "{$symbol} {$number}" : "{$symbol}{$number}";
    }
}
