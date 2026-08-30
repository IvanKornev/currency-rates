<?php

declare(strict_types=1);

namespace App\Services\Contracts;

interface CurrencyRateServiceInterface
{
    public function update(string $currencyCode): void;
}
