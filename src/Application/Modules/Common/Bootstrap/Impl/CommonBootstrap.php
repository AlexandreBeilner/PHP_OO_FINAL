<?php

declare(strict_types=1);

namespace App\Application\Modules\Common\Bootstrap\Impl;

use App\Application\Modules\Common\Bootstrap\BootstrapInterface;
use DI\ContainerBuilder;

final class CommonBootstrap extends AbstractBootstrap implements BootstrapInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $definitions = [
            // Database
            \App\Application\Modules\Common\Bootstrap\Impl\DoctrineServiceDefinition::class,
            
            // Common Services
            \App\Application\Modules\Common\Bootstrap\Impl\CommonServicesDefinition::class,
        ];

        $this->loadServiceDefinitions($builder, $definitions);
    }

    public function getModuleName(): string
    {
        return 'Common';
    }

    public function getPriority(): int
    {
        return 10; // Alta prioridade - serviços base
    }
}
