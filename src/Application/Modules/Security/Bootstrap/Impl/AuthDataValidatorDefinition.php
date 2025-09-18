<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\Bootstrap\Impl;

use App\Application\Modules\Common\Bootstrap\ServiceDefinitionInterface;
use App\Domain\Security\Validators\AuthDataValidatorInterface;
use App\Domain\Security\Validators\Impl\AuthDataValidator;
use DI\ContainerBuilder;

final class AuthDataValidatorDefinition implements ServiceDefinitionInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            AuthDataValidatorInterface::class => function () {
                return new AuthDataValidator();
            },
        ]);
    }
}
