<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\CurrencyRatePayload;
use App\Repositories\Contracts\CurrencyRateRepositoryInterface;
use JsonException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

final class CurrencyRateJsonRepository implements CurrencyRateRepositoryInterface
{
    public function __construct(
        private readonly Filesystem $filesystem,
        #[Autowire('%kernel.project_dir%/rates.json')]
        private readonly string $filePath,
    ) {}

    public function save(CurrencyRatePayload $payload): void
    {
        try {
            $content = json_encode($payload->toArray(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
            $this->filesystem->dumpFile($this->filePath, $content);
        } catch (JsonException $e) {
            throw new RuntimeException('Encode currency rates error', previous: $e);
        }
    }

    public function getAll(): array
    {
        if (!$this->filesystem->exists($this->filePath)) {
            return [];
        }

        $content = file_get_contents($this->filePath);

        if (empty($content)) {
            return [];
        }

        return json_decode($content, associative: true, flags: JSON_THROW_ON_ERROR) ?? [];
    }
}
