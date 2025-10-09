<?php

declare(strict_types=1);

namespace App\Application\Modules\Financial\Bootstrap\Impl;

use App\Application\Shared\ServiceDefinitionInterface;
use DI\ContainerBuilder;

final class FinancialValidationServiceDefinition implements ServiceDefinitionInterface
{

    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
        ]);
    }
}