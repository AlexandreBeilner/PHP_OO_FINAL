<?php

declare(strict_types=1);

namespace App\Application\Shared\Impl;

use App\Application\Shared\Http\Routing\Impl\RouteProviderFactory;
use App\Application\Shared\Http\Routing\RouteProviderFactoryInterface;
use App\Application\Shared\ServiceDefinitionInterface;
use DI\ContainerBuilder;
use function DI\create;

final class RouteProviderFactoryDefinition implements ServiceDefinitionInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            RouteProviderFactoryInterface::class => create(RouteProviderFactory::class),
        ]);
    }
}
