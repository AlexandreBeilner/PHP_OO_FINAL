<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\Bootstrap\Impl;

use App\Application\Shared\ServiceDefinitionInterface;
use App\Domain\Security\Services\AuthServiceInterface;
use App\Domain\Security\Services\Impl\AuthService;
use App\Domain\Security\Services\UserServiceInterface;
use DI\ContainerBuilder;

final class AuthServiceDefinition implements ServiceDefinitionInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            AuthServiceInterface::class => function ($container) {
                $userService = $container->get(UserServiceInterface::class);
                return new AuthService($userService);
            },
        ]);
    }
}
