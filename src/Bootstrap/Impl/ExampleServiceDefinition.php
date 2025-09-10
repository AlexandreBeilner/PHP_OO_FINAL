<?php

namespace App\Bootstrap\Impl;

use App\Bootstrap\ServiceDefinitionInterface;
use App\Service\Impl\IndexService;
use App\Service\IndexServiceInterface;
use DI\ContainerBuilder;

class ExampleServiceDefinition implements ServiceDefinitionInterface
{

    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            IndexServiceInterface::class => \DI\autowire(IndexService::class),
        ]);
    }
}
