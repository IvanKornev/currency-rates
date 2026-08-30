<?php

declare(strict_types=1);

namespace App\Commands;

use App\Services\Contracts\CurrencyServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
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
        private readonly CurrencyServiceInterface $currencyService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info('Fetching the currency rates');

        try {
            $this->currencyService->updateRates();
            $io->success('The currency rates were updated');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to update currency rates: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
