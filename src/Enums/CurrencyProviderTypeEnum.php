<?php

declare(strict_types=1);

namespace App\Enums;

enum CurrencyProviderTypeEnum: string
{
    case CRYPTO = 'crypto';
    case FIAT = 'fiat';
}
