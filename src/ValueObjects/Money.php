<?php

declare(strict_types=1);

namespace App\ValueObjects;

final class Money
{
    public const string DEFAULT_CURRENCY = 'USD';

    public static function isValidCurrencyCode(string $value): bool
    {
        return (bool) preg_match('/^[A-Z]{3}$/', $value);
    }
}
