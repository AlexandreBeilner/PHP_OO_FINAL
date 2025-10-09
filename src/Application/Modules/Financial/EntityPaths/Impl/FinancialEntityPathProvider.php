<?php

declare(strict_types=1);

namespace App\Application\Modules\Financial\EntityPaths\Impl;

use App\Application\Shared\EntityPaths\EntityPathProviderInterface;
use App\Application\Shared\Utils\Impl\ProjectRootDiscovery;

final class FinancialEntityPathProvider implements EntityPathProviderInterface
{
    private string $productsEntitiesPath;

    public function __construct()
    {
        $this->productsEntitiesPath = $this->buildFinancialEntityPath();
    }

    public function getEntityPaths(): array
    {
        if (!$this->hasEntityPaths()) {
            return [];
        }

        return [$this->productsEntitiesPath];
    }

    public function hasEntityPaths(): bool
    {
        return $this->isFinancialEntityPathValid();
    }

    private function buildFinancialEntityPath(): string
    {
        return ProjectRootDiscovery::getProjectRoot() . '/src/Domain/Financial/Entities/Impl';
    }

    private function isFinancialEntityPathValid(): bool
    {
        return is_dir($this->productsEntitiesPath);
    }
}
