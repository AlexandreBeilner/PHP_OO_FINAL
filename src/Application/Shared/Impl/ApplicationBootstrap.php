<?php

declare(strict_types=1);

namespace App\Application\Shared\Impl;

use App\Application\Shared\BootstrapInterface;
use DI\ContainerBuilder;

final class ApplicationBootstrap extends AbstractBootstrap implements BootstrapInterface
{
    public function belongsToModule(string $moduleName): bool
    {
        return $moduleName === 'Application';
    }

    public function getModuleName(): string
    {
        return 'Application';
    }

    public function getPriority(): int
    {
        return 50; // Baixa prioridade - depende de todos os outros módulos
    }

    public function hasPriorityOver(BootstrapInterface $other): bool
    {
        // Application tem prioridade 50 - só tem prioridade sobre AbstractBootstrap (100+)
        // Perde para Common (10), System (20), Auth (25), Security (30)
        return false; // Na prática, todos os outros têm prioridade maior
    }

    public function register(ContainerBuilder $builder): void
    {
        // Application layer não possui definições específicas
        // Apenas coordena os outros módulos
    }
}
