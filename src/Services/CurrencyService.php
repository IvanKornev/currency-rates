<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\CurrencyRates;
use App\DTO\CurrencyRatePayload;
use App\Providers\Contracts\CurrencyRateProviderInterface;
use App\Repositories\Contracts\CurrencyRateRepositoryInterface;
use App\Services\Contracts\CurrencyServiceInterface;
use Exception;
use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class CurrencyService implements CurrencyServiceInterface
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

    public function updateRates(): void
    {
        $rates = [];
        $successCount = 0;

        foreach ($this->providers as $provider) {
            try {
                $type = $provider->getType()->value;

                if (isset($rates[$type])) {
                    throw new LogicException("Duplicate currency rate provider type \"$type\" detected");
                }

                $rates[$type] = $provider->fetch();
                $successCount++;
            } catch (LogicException $e) {
                throw $e;
            } catch (\Throwable $e) {
                $this->logger?->warning('Failed to fetch currency rate {currency} from {provider}', [
                    'provider' => $provider::class,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($successCount === 0) {
            throw new \RuntimeException('All currency rate providers failed for code');
        }

        $payload = new CurrencyRatePayload(updatedAt: new \DateTimeImmutable(), rates: $rates);
        $this->ratesRepository->save($payload);
    }

    public function getAllRates(): CurrencyRates
    {
        $data = $this->ratesRepository->getAll();

        if (!$data) {
            throw new Exception('Rates data not found. Please run currency:update-rates first');
        }

        return CurrencyRates::fromStorage($data);
    }
}
