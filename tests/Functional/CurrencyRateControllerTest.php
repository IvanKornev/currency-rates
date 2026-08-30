<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controllers;

use App\Repositories\Contracts\CurrencyRateRepositoryInterface;
use App\ValueObjects\Money;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class CurrencyRateControllerTest extends WebTestCase
{
    private readonly Stub & CurrencyRateRepositoryInterface $stubRepository;
    private readonly KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $this->stubRepository = $this->createStub(CurrencyRateRepositoryInterface::class);
        static::getContainer()->set(CurrencyRateRepositoryInterface::class, $this->stubRepository);
    }

    public function testGetAllCurrencyRates(): void
    {
        $this->stubRepository->method('getAll')->willReturn(['fiat' => ['EUR' => 0.85]]);

        $baseCurrency = Money::DEFAULT_CURRENCY;
        $this->client->request('GET', '/api/currency/rates', ['base' => $baseCurrency]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($content);
        $this->assertContainsEquals(['rate' => 1, 'code' => $baseCurrency], $content);
        $this->assertContainsEquals(['rate' => 0.85, 'code' => 'EUR'], $content);
    }

    public function testConvertSuccessfully(): void
    {
        $this->stubRepository->method('getAll')->willReturn(['fiat' => ['EUR' => 0.85, 'GBP' => 0.75]]);

        $this->client->request('GET', '/api/currency/convert', [
            'amount' => 100,
            'from' => 'EUR',
            'to' => 'GBP'
        ]);

        $this->assertResponseIsSuccessful();

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('amount', $content);
        $this->assertEquals(88.24, $content['amount']);
        $this->assertEquals('EUR', $content['currency_from']['code']);
        $this->assertEquals('GBP', $content['currency_to']['code']);
    }

    public function testConvertReturnsBadRequestOnInvalidCurrency(): void
    {
        $this->stubRepository->method('getAll')->willReturn(['fiat' => ['EUR' => 0.85, 'GBP' => 0.75]]);

        $this->client->request('GET', '/api/currency/convert', [
            'amount' => 100,
            'from' => 'AAA',
            'to' => 'GBP'
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
