<?php

declare(strict_types=1);

namespace App\DTO\Request;

use App\ValueObjects\Money;
use Symfony\Component\Validator\Constraints;

final class CurrencyRateRequestDto
{
    public function __construct(
        public readonly string $base = Money::DEFAULT_CURRENCY,
    ) {}

    public function getBase(): string
    {
        return strtoupper($this->base);
    }
}
