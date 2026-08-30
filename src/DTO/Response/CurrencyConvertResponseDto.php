<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Domain\CurrencyRates;
use App\DTO\Request\CurrencyConvertRequestDto;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class CurrencyConvertResponseDto
{
    public function __construct(
        #[OA\Property(example: 92.45)]
        public float $amount,

        #[OA\Property(ref: new Model(type: CurrencyRateResponseDto::class))]
        #[SerializedName('currency_from')]
        public CurrencyRateResponseDto $currencyFrom,

        #[OA\Property(ref: new Model(type: CurrencyRateResponseDto::class))]
        #[SerializedName('currency_to')]
        public CurrencyRateResponseDto $currencyTo,
    ) {}

    public static function make(CurrencyConvertRequestDto $data, CurrencyRates $rates): self
    {
        $from = $data->getFrom();
        $to = $data->getTo();

        $moneyTo = $rates->convert($data->getAmount(), $from, $to);
        $crossRate = $rates->getRate($to) / $rates->getRate($from);

        return new self(
            amount: (float) $moneyTo->getAmount(),
            currencyFrom: new CurrencyRateResponseDto(code: $from, rate: 1.0),
            currencyTo: new CurrencyRateResponseDto(code: $to, rate: $crossRate),
        );
    }
}
