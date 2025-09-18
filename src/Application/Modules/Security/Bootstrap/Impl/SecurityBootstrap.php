<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\Bootstrap\Impl;

use App\Application\Modules\Common\Bootstrap\BootstrapInterface;
use App\Application\Modules\Common\Bootstrap\Impl\AbstractBootstrap;
use DI\ContainerBuilder;

final class SecurityBootstrap extends AbstractBootstrap implements BootstrapInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $definitions = [
            // Security Services
            \App\Application\Modules\Security\Bootstrap\Impl\UserServiceDefinition::class,
            \App\Application\Modules\Security\Bootstrap\Impl\AuthServiceDefinition::class,
            \App\Application\Modules\Security\Bootstrap\Impl\UserValidationServiceDefinition::class,
            \App\Application\Modules\Security\Bootstrap\Impl\AuthValidationServiceDefinition::class,
            
            // Security Validators
            \App\Application\Modules\Security\Bootstrap\Impl\UserDataValidatorDefinition::class,
            \App\Application\Modules\Security\Bootstrap\Impl\AuthDataValidatorDefinition::class,
            
            // Security Controllers
            \App\Application\Modules\Security\Bootstrap\Impl\UserControllerDefinition::class,
            \App\Application\Modules\Security\Bootstrap\Impl\AuthControllerDefinition::class,
        ];

        $this->loadServiceDefinitions($builder, $definitions);
    }

    public function getModuleName(): string
    {
        return 'Security';
    }

    public function getPriority(): int
    {
        return 30; // Prioridade baixa - depende de Common
    }
}
