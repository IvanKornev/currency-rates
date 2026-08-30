<?php

declare(strict_types=1);

namespace App\Domain;

use App\Enums\CurrencyProviderTypeEnum;
use App\ValueObjects\Money;

final class CurrencyRates
{
    /**
     * @param array<string, float> $rates
     */
    private function __construct(private readonly array $rates) {}

    public static function fromStorage(array $data): self
    {
        $rates = [Money::DEFAULT_CURRENCY => 1.0];

        foreach (CurrencyProviderTypeEnum::cases() as $case) {
            $type = $case->value;
            if (!empty($data[$type]) && is_array($data[$type])) {
                foreach ($data[$type] as $code => $rate) {
                    $rates[strtoupper((string) $code)] = (float) $rate;
                }
            }
        }

        return new self($rates);
    }

    public function convert(float $amount, string $from, string $to): Money
    {
        $rateFrom = $this->getRate($from);
        $rateTo = $this->getRate($to);

        $converted = $amount * ($rateTo / $rateFrom);

        return Money::of((string) $converted, $to);
    }

    public function getRate(string $currency): float
    {
        $currency = strtoupper($currency);

        if (!isset($this->rates[$currency])) {
            throw new \DomainException("Rate for currency {$currency} not found");
        }

        return $this->rates[$currency];
    }

    public function getAllRates(): array
    {
        return $this->rates;
    }
}
