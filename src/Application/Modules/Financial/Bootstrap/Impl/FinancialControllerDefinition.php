<?php

declare(strict_types=1);

namespace App\Application\Modules\Financial\Bootstrap\Impl;

use App\Application\Modules\Financial\Controllers\BankAccountControllerInterface;
use App\Application\Modules\Financial\Controllers\Impl\BankAccountController;
use App\Application\Shared\ServiceDefinitionInterface;
use DI\ContainerBuilder;

final class FinancialControllerDefinition implements ServiceDefinitionInterface
{

    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            BankAccountControllerInterface::class => function ($container) {
                return new BankAccountController();
            },
        ]);
    }
}