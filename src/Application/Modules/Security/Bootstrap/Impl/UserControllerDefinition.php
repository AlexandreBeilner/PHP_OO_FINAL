<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\Bootstrap\Impl;

use App\Application\Modules\Common\Bootstrap\ServiceDefinitionInterface;
use App\Application\Modules\Security\Controllers\Impl\UserController;
use App\Application\Modules\Security\Controllers\UserControllerInterface;
use App\Domain\Security\Services\UserServiceInterface;
use App\Domain\Security\Services\UserValidationServiceInterface;
use DI\ContainerBuilder;

final class UserControllerDefinition implements ServiceDefinitionInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            UserControllerInterface::class => function ($container) {
                $userService = $container->get(UserServiceInterface::class);
                $userValidationService = $container->get(UserValidationServiceInterface::class);
                return new UserController($userService, $userValidationService);
            },
        ]);
    }
}
