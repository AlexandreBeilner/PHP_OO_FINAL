<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\Bootstrap\Impl;

use App\Application\Shared\ServiceDefinitionInterface;
use App\Infrastructure\Common\Database\DoctrineEntityManagerInterface;
use App\Domain\Common\Validators\Impl\EmailValidator;
use App\Domain\Security\Repositories\Impl\UserRepository;
use App\Domain\Security\Repositories\UserRepositoryInterface;
use App\Domain\Security\Services\Impl\UserService;
use App\Domain\Security\Services\UserServiceInterface;
use DI\ContainerBuilder;

final class UserServiceDefinition implements ServiceDefinitionInterface
{
    public function register(ContainerBuilder $builder): void
    {
        // User Repository
        $builder->addDefinitions([
            UserRepositoryInterface::class => function ($container) {
                $doctrineManager = $container->get(DoctrineEntityManagerInterface::class);
                return new UserRepository($doctrineManager->getMaster());
            },
        ]);

        // User Service
        $builder->addDefinitions([
            UserServiceInterface::class => function ($container) {
                $repository = $container->get(UserRepositoryInterface::class);
                $emailValidator = new EmailValidator();
                return new UserService($repository, $emailValidator);
            },
        ]);
    }
}
