<?php

declare(strict_types=1);

namespace App\Application\Modules\Common\Bootstrap\Impl;

use App\Application\Modules\Common\Bootstrap\BootstrapInterface;
use DI\ContainerBuilder;

final class ApplicationBootstrap extends AbstractBootstrap implements BootstrapInterface
{
    public function register(ContainerBuilder $builder): void
    {
        // Application layer não possui definições específicas
        // Apenas coordena os outros módulos
    }

    public function getModuleName(): string
    {
        return 'Application';
    }

    public function getPriority(): int
    {
        return 50; // Baixa prioridade - depende de todos os outros módulos
    }
}
