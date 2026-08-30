<?php

declare(strict_types=1);

namespace App\Providers\Contracts;

use App\Enums\CurrencyProviderTypeEnum;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.currency_rate_provider')]
interface CurrencyRateProviderInterface
{
    public function getType(): CurrencyProviderTypeEnum;

    public function fetch(string $currencyCode): array;
}
