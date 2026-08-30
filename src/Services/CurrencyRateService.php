<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\CurrencyRatePayload;
use App\Providers\Contracts\CurrencyRateProviderInterface;
use App\Repositories\Contracts\CurrencyRateRepositoryInterface;
use App\Services\Contracts\CurrencyRateServiceInterface;
use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class CurrencyRateService implements CurrencyRateServiceInterface
{
    /**
     * @param iterable<CurrencyRateProviderInterface> $providers
     */
    public function __construct(
        #[AutowireIterator('app.currency_rate_provider')]
        private readonly iterable $providers,
        private readonly CurrencyRateRepositoryInterface $ratesRepository,
        private readonly ?LoggerInterface $logger = null,
    ) {}

    public function update(string $currencyCode): void
    {
        $rates = [];
        $successCount = 0;

        foreach ($this->providers as $provider) {
            try {
                $type = $provider->getType()->value;

                if (isset($rates[$type])) {
                    throw new LogicException("Duplicate currency rate provider type \"$type\" detected");
                }

                $rates[$type] = $provider->fetch($currencyCode);
                $successCount++;
            } catch (LogicException $e) {
                throw $e;
            } catch (\Throwable $e) {
                $this->logger?->warning('Failed to fetch currency rate {currency} from {provider}', [
                    'provider' => $provider::class,
                    'currency' => $currencyCode,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($successCount === 0) {
            throw new \RuntimeException("All currency rate providers failed for code $currencyCode");
        }

        $payload = new CurrencyRatePayload(
            updatedAt: new \DateTimeImmutable(),
            rates: $rates
        );

        $this->ratesRepository->save($currencyCode, $payload);
    }
}
