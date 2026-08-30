<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTO\CurrencyRatePayload;

interface CurrencyRateRepositoryInterface
{
    public function save(string $currencyCode, CurrencyRatePayload $payload): void;

    public function get(string $currencyCode): ?array;
}
