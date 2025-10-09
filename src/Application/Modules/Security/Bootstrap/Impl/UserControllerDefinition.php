<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\Bootstrap\Impl;

use App\Application\Modules\Security\Controllers\UserControllerInterface;
use App\Application\Modules\Security\Factories\Impl\UserCommandExecutor;
use App\Application\Modules\Security\Factories\Impl\UserCrudOperationFactory;
use App\Application\Modules\Security\Factories\Impl\UserRequestValidator;
use App\Application\Shared\Controllers\Crud\ExceptionHandlerInterface;
use App\Application\Shared\Controllers\Crud\HttpRequestHandlerInterface;
use App\Application\Shared\Controllers\Crud\Impl\GenericCrudController;
use App\Application\Shared\Controllers\Crud\Impl\StandardExceptionHandler;
use App\Application\Shared\Controllers\Crud\Impl\StandardHttpRequestHandler;
use App\Application\Shared\ServiceDefinitionInterface;
use App\Domain\Security\Services\UserServiceInterface;
use App\Domain\Security\Services\UserValidationServiceInterface;
use DI\ContainerBuilder;

final class UserControllerDefinition implements ServiceDefinitionInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            // Exception Handler - shared singleton
            ExceptionHandlerInterface::class => function (): ExceptionHandlerInterface {
                return new StandardExceptionHandler();
            },

            // HTTP Request Handler - shared singleton  
            HttpRequestHandlerInterface::class => function ($container): HttpRequestHandlerInterface {
                $exceptionHandler = $container->get(ExceptionHandlerInterface::class);
                return new StandardHttpRequestHandler($exceptionHandler);
            },

            // User specific implementations
            UserRequestValidator::class => function ($container): UserRequestValidator {
                $userValidationService = $container->get(UserValidationServiceInterface::class);
                return new UserRequestValidator($userValidationService);
            },

            UserCommandExecutor::class => function ($container): UserCommandExecutor {
                $userService = $container->get(UserServiceInterface::class);
                return new UserCommandExecutor($userService);
            },

            UserCrudOperationFactory::class => function ($container): UserCrudOperationFactory {
                $validator = $container->get(UserRequestValidator::class);
                $executor = $container->get(UserCommandExecutor::class);
                return new UserCrudOperationFactory($validator, $executor);
            },

            // Controller using new CRUD system
            UserControllerInterface::class => function ($container): UserControllerInterface {
                $requestHandler = $container->get(HttpRequestHandlerInterface::class);
                $operationFactory = $container->get(UserCrudOperationFactory::class);
                return new GenericCrudController($requestHandler, $operationFactory);
            },
        ]);
    }
}
