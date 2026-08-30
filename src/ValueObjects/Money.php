<?php

declare(strict_types=1);

namespace App\ValueObjects;

use Brick\Math\RoundingMode;
use Brick\Money\Context\DefaultContext;
use Brick\Money\Currency;
use Brick\Money\CurrencyType;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money as MoneyBase;

final class Money
{
    private const int FALLBACK_FRACTION_DIGITS = 8;
    public const string DEFAULT_CURRENCY = 'USD';

    private function __construct(
        private readonly MoneyBase $money,
    ) {}

    public static function of(mixed $amount, string $currency = self::DEFAULT_CURRENCY): self
    {
        try {
            $currency = Currency::of($currency);
        } catch (UnknownCurrencyException) {
            $currency = new Currency(
                currencyCode: strtoupper($currency),
                numericCode: null,
                name: strtoupper($currency),
                defaultFractionDigits: self::FALLBACK_FRACTION_DIGITS,
                currencyType: CurrencyType::Custom,
            );
        }

        return new self(MoneyBase::of($amount, $currency, new DefaultContext(), RoundingMode::HalfUp));
    }

    public static function ofMinor(int|string $amount, string $currency = self::DEFAULT_CURRENCY): self
    {
        return new self(MoneyBase::ofMinor($amount, $currency));
    }

    public function equals(self $other): bool
    {
        return $this->money->isEqualTo($other->toMoneyBase());
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->money->isGreaterThan($other->toMoneyBase());
    }

    public function isZero(): bool
    {
        return $this->money->isZero();
    }

    public function isPositive(): bool
    {
        return $this->money->isPositive();
    }

    public function isLessThan(self $other): bool
    {
        return $this->money->isLessThan($other->toMoneyBase());
    }

    public function isNegative(): bool
    {
        return $this->money->isNegative();
    }

    public function getAmount(): string
    {
        return (string) $this->money->getAmount();
    }

    public function getMinorAmount(): string
    {
        return (string) $this->money->getMinorAmount();
    }

    public function getCurrencyCode(): string
    {
        return $this->money->getCurrency()->getCurrencyCode();
    }

    public function add(self $other): self
    {
        return new self($this->money->plus($other->toMoneyBase()));
    }

    public function subtract(self $other): self
    {
        return new self($this->money->minus($other->toMoneyBase()));
    }

    public function multiply(mixed $multiplier, RoundingMode $roundingMode = RoundingMode::HalfUp): self
    {
        return new self($this->money->multipliedBy($multiplier, $roundingMode));
    }

    public function divide(mixed $divisor, RoundingMode $roundingMode = RoundingMode::HalfUp): self
    {
        return new self($this->money->dividedBy($divisor, $roundingMode));
    }

    public function toMoneyBase(): MoneyBase
    {
        return $this->money;
    }

    public function __toString(): string
    {
        return (string) $this->money;
    }
}
