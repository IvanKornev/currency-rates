<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\CurrencyProviderTypeEnum;
use App\Providers\Contracts\CurrencyRateProviderInterface;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class FloatRatesProvider implements CurrencyRateProviderInterface
{
    private const BASE_URL = 'https://www.floatrates.com';

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
        return CurrencyProviderTypeEnum::FIAT;
    }

    public function fetch(string $currencyCode): array
    {
        $url = self::BASE_URL . "/daily/$currencyCode.json";
        $response = $this->httpClient->request('GET', $url);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new \RuntimeException("FloatRates error: $currencyCode");
        }

        return $this->adapt($response->toArray());
    }

    private function adapt(array $fiatData): array
    {
        $rates = [];

        foreach ($fiatData as $currency => $data) {
            $rates[strtoupper($currency)] = $data['rate'];
        }

        return $rates;
    }
}
