<?php

declare(strict_types=1);

namespace App\Application\Modules\System\Bootstrap\Impl;

use App\Application\Modules\Common\Bootstrap\ServiceDefinitionInterface;
use App\Common\Database\DoctrineEntityManagerInterface;
use App\Domain\System\Services\Impl\SystemService;
use App\Domain\System\Services\SystemServiceInterface;
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
