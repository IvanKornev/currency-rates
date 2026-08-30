<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Domain\CurrencyRates;
use App\ValueObjects\Money;
use OpenApi\Attributes as OA;

final readonly class CurrencyRateResponseDto
{
    public function __construct(
        #[OA\Property(example: Money::DEFAULT_CURRENCY)]
        public string $code,

        #[OA\Property(example: 1.0)]
        public float $rate,
    ) {}

    public static function makeCollection(string $baseCurrency, CurrencyRates $rates): array
    {
        $baseMultiplier = $rates->getRate($baseCurrency);
        $collection = [new self(code: $baseCurrency, rate: 1.0)];

        foreach ($rates->getAllRates() as $code => $rate) {
            if ($code === $baseCurrency) {
                continue;
            }

            $collection[] = new self(code: $code, rate: $rate / $baseMultiplier);
        }

        return $collection;
    }
}
