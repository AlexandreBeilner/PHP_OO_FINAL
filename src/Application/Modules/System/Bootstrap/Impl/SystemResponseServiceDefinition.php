<?php

declare(strict_types=1);

namespace App\Application\Modules\System\Bootstrap\Impl;

use App\Application\Shared\ServiceDefinitionInterface;
use App\Domain\System\Services\Impl\SystemResponseService;
use App\Domain\System\Services\SystemResponseServiceInterface;
use DI\ContainerBuilder;

final class SystemResponseServiceDefinition implements ServiceDefinitionInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            SystemResponseServiceInterface::class => function () {
                return new SystemResponseService();
            },
        ]);
    }
}
