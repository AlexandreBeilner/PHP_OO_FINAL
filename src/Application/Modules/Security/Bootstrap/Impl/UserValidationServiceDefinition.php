<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\Bootstrap\Impl;

use App\Application\Shared\ServiceDefinitionInterface;
use App\Domain\Security\Services\UserValidationServiceInterface;
use App\Domain\Security\Services\Impl\UserValidationService;
use App\Domain\Security\Validators\UserDataValidatorInterface;
use DI\ContainerBuilder;

final class UserValidationServiceDefinition implements ServiceDefinitionInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            UserValidationServiceInterface::class => function ($container) {
                $userDataValidator = $container->get(UserDataValidatorInterface::class);
                return new UserValidationService($userDataValidator);
            },
        ]);
    }
}
