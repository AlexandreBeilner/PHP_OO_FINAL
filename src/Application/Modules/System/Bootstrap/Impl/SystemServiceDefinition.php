<?php

declare(strict_types=1);

namespace App\Application\Modules\System\Bootstrap\Impl;

use App\Application\Shared\ServiceDefinitionInterface;
use App\Domain\System\Services\Impl\SystemService;
use App\Domain\System\Services\SystemServiceInterface;
use App\Infrastructure\Common\Database\DoctrineEntityManagerInterface;
use DI\ContainerBuilder;

final class SystemServiceDefinition implements ServiceDefinitionInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            SystemServiceInterface::class => function ($container) {
                $doctrineManager = $container->get(DoctrineEntityManagerInterface::class);
                return new SystemService($doctrineManager);
            },
        ]);
    }
}
