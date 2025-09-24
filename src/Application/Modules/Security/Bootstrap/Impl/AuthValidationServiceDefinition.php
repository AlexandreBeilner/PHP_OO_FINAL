<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\Bootstrap\Impl;

use App\Application\Shared\ServiceDefinitionInterface;
use App\Domain\Security\Services\AuthValidationServiceInterface;
use App\Domain\Security\Services\Impl\AuthValidationService;
use App\Domain\Security\Validators\AuthDataValidatorInterface;
use DI\ContainerBuilder;

final class AuthValidationServiceDefinition implements ServiceDefinitionInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            AuthValidationServiceInterface::class => function ($container) {
                $authDataValidator = $container->get(AuthDataValidatorInterface::class);
                return new AuthValidationService($authDataValidator);
            },
        ]);
    }
}
