<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services;

use App\DTO\CurrencyRatePayload;
use App\Enums\CurrencyProviderTypeEnum;
use App\Providers\Contracts\CurrencyRateProviderInterface;
use App\Repositories\Contracts\CurrencyRateRepositoryInterface;
use App\Services\CurrencyService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class CurrencyServiceTest extends TestCase
{
    private readonly MockObject & CurrencyRateRepositoryInterface $repository;
    private readonly LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CurrencyRateRepositoryInterface::class);
        $this->logger = $this->createStub(LoggerInterface::class);
    }

    public function testUpdateSuccessfullyAggregatesRates(): void
    {
        $fiatProvider = $this->createStub(CurrencyRateProviderInterface::class);
        $fiatProvider->method('getType')->willReturn(CurrencyProviderTypeEnum::FIAT);
        $fiatProvider->method('fetch')->willReturn(['EUR' => 0.85]);

        $cryptoProvider = $this->createStub(CurrencyRateProviderInterface::class);
        $cryptoProvider->method('getType')->willReturn(CurrencyProviderTypeEnum::CRYPTO);
        $cryptoProvider->method('fetch')->willReturn(['BTC' => 0.00002]);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (CurrencyRatePayload $payload): bool {
                $rates = $payload->toArray();
                return isset($rates['fiat']['EUR']) && isset($rates['crypto']['BTC']);
            }));

        $service = new CurrencyService([$fiatProvider, $cryptoProvider], $this->repository, $this->logger);
        $service->updateRates();
    }

    public function testUpdateThrowsExceptionWhenAnyProviderFail(): void
    {
        $fiatProvider = $this->createStub(CurrencyRateProviderInterface::class);
        $fiatProvider->method('getType')->willReturn(CurrencyProviderTypeEnum::FIAT);
        $fiatProvider->method('fetch')->willThrowException(new RuntimeException('API Error'));

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())->method('error');

        $this->repository->expects($this->never())->method('save');

        $service = new CurrencyService([$fiatProvider], $this->repository, $loggerMock);
        $this->expectException(RuntimeException::class);
        $service->updateRates();
    }
}
