<?php

declare(strict_types=1);

namespace App\Application\Modules\System\Bootstrap\Impl;

use App\Application\Modules\Common\Bootstrap\BootstrapInterface;
use App\Application\Modules\Common\Bootstrap\Impl\AbstractBootstrap;
use DI\ContainerBuilder;

final class SystemBootstrap extends AbstractBootstrap implements BootstrapInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $definitions = [
            // System Services
            \App\Application\Modules\System\Bootstrap\Impl\SystemServiceDefinition::class,
            \App\Application\Modules\System\Bootstrap\Impl\SystemResponseServiceDefinition::class,
            
            // System Controllers
            \App\Application\Modules\System\Bootstrap\Impl\SystemControllerDefinition::class,
        ];

        $this->loadServiceDefinitions($builder, $definitions);
    }

    public function getModuleName(): string
    {
        return 'System';
    }

    public function getPriority(): int
    {
        return 20; // Prioridade média
    }
}
