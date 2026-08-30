<?php

declare(strict_types=1);

namespace App\Services\Contracts;

interface CurrencyServiceInterface
{
    public function updateRates(): void;

    public function getAllRates();
}
