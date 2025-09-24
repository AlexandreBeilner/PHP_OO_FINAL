<?php

declare(strict_types=1);

namespace App\Application\Modules\System\Bootstrap\Impl;

use App\Application\Shared\ServiceDefinitionInterface;
use App\Application\Modules\System\Controllers\Impl\SystemController;
use App\Application\Modules\System\Controllers\SystemControllerInterface;
use App\Domain\System\Services\SystemServiceInterface;
use App\Domain\System\Services\SystemResponseServiceInterface;
use DI\ContainerBuilder;

final class SystemControllerDefinition implements ServiceDefinitionInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            SystemControllerInterface::class => function ($container) {
                $systemService = $container->get(SystemServiceInterface::class);
                $systemResponseService = $container->get(SystemResponseServiceInterface::class);
                return new SystemController($systemService, $systemResponseService);
            },
            SystemController::class => function ($container) {
                return $container->get(SystemControllerInterface::class);
            },
        ]);
    }
}
