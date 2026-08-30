<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTO\Request\CurrencyConvertRequestDto;
use App\DTO\Request\CurrencyRateRequestDto;
use App\DTO\Response\CurrencyConvertResponseDto;
use App\DTO\Response\CurrencyRateResponseDto;
use App\Services\Contracts\CurrencyServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/currency')]
#[OA\Tag(name: 'Currency')]
final class CurrencyController extends AbstractController
{
    public function __construct(
        private readonly CurrencyServiceInterface $currencyService,
    ) {}

    #[Route('/rates', name: 'currency_rates_get_all', methods: ['GET'])]
    #[OA\Get(
        path: '/api/currency/rates',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Currency Rates List',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: CurrencyRateResponseDto::class)))
            )
        ]
    )]
    public function getAll(#[MapQueryString] CurrencyRateRequestDto $request): JsonResponse
    {
        $rates = $this->currencyService->getAllRates();
        $response = CurrencyRateResponseDto::makeCollection($request->getBase(), $rates);

        return $this->json($response);
    }

    #[Route('/convert', name: 'currency_convert', methods: ['GET'])]
    #[OA\Get(
        path: '/api/currency/convert',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Currency Conversion Results',
                content: new OA\JsonContent(ref: new Model(type: CurrencyConvertResponseDto::class))
            )
        ]
    )]
    public function convert(#[MapQueryString] CurrencyConvertRequestDto $query): JsonResponse
    {
        $rates = $this->currencyService->getAllRates();
        $response = CurrencyConvertResponseDto::make($query, $rates);

        return $this->json($response);
    }
}
