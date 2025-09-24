<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\Bootstrap\Impl;

use App\Application\Shared\Controllers\Crud\HttpRequestHandlerInterface;
use App\Application\Shared\Controllers\Crud\Impl\SimpleAuthController;
use App\Application\Shared\Controllers\Crud\Impl\SimpleAuthOperations;
use App\Application\Modules\Auth\Controllers\AuthControllerInterface;
use App\Application\Shared\ServiceDefinitionInterface;
use App\Domain\Security\Services\AuthServiceInterface;
use App\Domain\Security\Services\AuthValidationServiceInterface;
use App\Domain\Security\Services\UserServiceInterface;
use DI\ContainerBuilder;

final class AuthControllerDefinition implements ServiceDefinitionInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            // Simple Auth Operations - apenas para login e change password
            SimpleAuthOperations::class => function ($container): SimpleAuthOperations {
                $userService = $container->get(UserServiceInterface::class);
                $authService = $container->get(AuthServiceInterface::class);
                $authValidationService = $container->get(AuthValidationServiceInterface::class);
                return new SimpleAuthOperations($userService, $authService, $authValidationService);
            },
            
            // Controller com operações específicas
            AuthControllerInterface::class => function ($container): AuthControllerInterface {
                $requestHandler = $container->get(HttpRequestHandlerInterface::class);
                $userService = $container->get(UserServiceInterface::class);
                $authOperations = $container->get(SimpleAuthOperations::class);
                return new SimpleAuthController($requestHandler, $userService, $authOperations);
            },
        ]);
    }
}
