<?php

declare(strict_types=1);

namespace App\Commands;

use App\Services\Contracts\CurrencyRateServiceInterface;
use App\ValueObjects\Money;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'currency:update-rates',
    description: 'Fetches the currency rates and saves them to a storage.'
)]
final class UpdateCurrencyRatesCommand extends Command
{
    public function __construct(
        private readonly CurrencyRateServiceInterface $ratesService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            name: 'currency_code',
            mode: InputArgument::OPTIONAL,
            description: 'Currency code for new rates',
            default: Money::DEFAULT_CURRENCY,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $currencyCode = strtoupper($input->getArgument('currency_code'));

        if (!Money::isValidCurrencyCode($currencyCode)) {
            $io->error("Invalid code \"$currencyCode\". It must be a valid 3-letter ISO code (e.g., USD, EUR).");

            return Command::INVALID;
        }

        $io->info("Fetching the currency rates for $currencyCode...");

        try {
            $this->ratesService->update($currencyCode);
            $io->success("The currency rates were updated for $currencyCode");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to update currency rates: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
