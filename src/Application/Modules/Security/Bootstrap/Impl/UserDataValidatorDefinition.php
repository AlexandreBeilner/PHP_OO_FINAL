<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\Bootstrap\Impl;

use App\Application\Shared\ServiceDefinitionInterface;
use App\Domain\Security\Validators\UserDataValidatorInterface;
use App\Domain\Security\Validators\Impl\UserDataValidator;
use DI\ContainerBuilder;

final class UserDataValidatorDefinition implements ServiceDefinitionInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            UserDataValidatorInterface::class => function () {
                return new UserDataValidator();
            },
        ]);
    }
}
