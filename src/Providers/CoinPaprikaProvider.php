<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\CurrencyProviderTypeEnum;
use App\Providers\Contracts\CurrencyRateProviderInterface;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CoinPaprikaProvider implements CurrencyRateProviderInterface
{
    private const BASE_URL = 'https://api.coinpaprika.com/v1';

    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = new RetryableHttpClient(
            client: $httpClient,
            maxRetries: 3,
        );
    }

    public function getType(): CurrencyProviderTypeEnum
    {
        return CurrencyProviderTypeEnum::CRYPTO;
    }

    public function fetch(string $currencyCode): array
    {
        $url = self::BASE_URL . "/exchanges/coinbase/markets?quotes=$currencyCode";
        $response = $this->httpClient->request('GET', $url);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new \RuntimeException("CoinPaprikaProvider error: $currencyCode");
        }

        return $this->adapt($response->toArray(), $currencyCode);
    }

    private function adapt(array $cryptoData, string $currencyCode): array
    {
        $rates = [];

        foreach ($cryptoData as $market) {
            //
        }

        return $rates;
    }
}
