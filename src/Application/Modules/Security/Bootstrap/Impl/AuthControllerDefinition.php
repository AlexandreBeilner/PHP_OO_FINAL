<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\Bootstrap\Impl;

use App\Application\Modules\Common\Bootstrap\ServiceDefinitionInterface;
use App\Application\Modules\Auth\Controllers\Impl\AuthController;
use App\Application\Modules\Auth\Controllers\AuthControllerInterface;
use App\Domain\Security\Services\AuthServiceInterface;
use App\Domain\Security\Services\UserServiceInterface;
use App\Domain\Security\Services\AuthValidationServiceInterface;
use DI\ContainerBuilder;

final class AuthControllerDefinition implements ServiceDefinitionInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            AuthControllerInterface::class => function ($container) {
                $authService = $container->get(AuthServiceInterface::class);
                $userService = $container->get(UserServiceInterface::class);
                $authValidationService = $container->get(AuthValidationServiceInterface::class);
                return new AuthController($authService, $userService, $authValidationService);
            },
            AuthController::class => function ($container) {
                return $container->get(AuthControllerInterface::class);
            },
        ]);
    }
}
