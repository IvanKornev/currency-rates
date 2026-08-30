<?php

declare(strict_types=1);

namespace App\DTO\Request;

use App\ValueObjects\Money;
use Symfony\Component\Validator\Constraints;

final class CurrencyConvertRequestDto
{
    public function __construct(
        #[Constraints\Type('float')]
        #[Constraints\Positive]
        public readonly float $amount,

        #[Constraints\NotBlank]
        public readonly string $from,

        #[Constraints\NotBlank]
        public readonly string $to,
    ) {}

    public function getFrom(): string
    {
        return strtoupper($this->from);
    }

    public function getTo(): string
    {
        return strtoupper($this->to);
    }

    public function getAmount(): float
    {
        return $this->amount;
    }
}
