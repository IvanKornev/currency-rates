<?php

declare(strict_types=1);

namespace App\DTO;

final class CurrencyRatePayload
{
    /**
     * @param array<string, mixed> $rates
     */
    public function __construct(
        public readonly \DateTimeImmutable $updatedAt,
        public readonly array $rates = [],
    ) {}

    public function toArray(): array
    {
        return array_merge(
            ['updated_at' => $this->updatedAt->format(\DateTimeInterface::ATOM)],
            $this->rates
        );
    }
}
