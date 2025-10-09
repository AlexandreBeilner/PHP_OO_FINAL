<?php

declare(strict_types=1);

namespace App\Application\Modules\Financial\Bootstrap\Impl;

use App\Application\Shared\ServiceDefinitionInterface;
use App\Domain\Financial\Repositories\Impl\BankAccountRepository;
use App\Domain\Financial\Repositories\Impl\BankAccountRepositoryInterface;
use App\Infrastructure\Common\Database\DoctrineEntityManagerInterface;
use DI\ContainerBuilder;

final class FinancialServiceDefinition implements ServiceDefinitionInterface
{

    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            BankAccountRepositoryInterface::class => function ($container) {
                $doctrineManager = $container->get(DoctrineEntityManagerInterface::class);
                return new BankAccountRepository($doctrineManager);
            }
        ]);
    }
}