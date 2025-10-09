<?php

declare(strict_types=1);

namespace App\Application\Modules\Products\Bootstrap\Impl;

use App\Application\Shared\ServiceDefinitionInterface;
use App\Domain\Products\Repositories\Impl\ProductRepository;
use App\Domain\Products\Repositories\ProductRepositoryInterface;
use App\Domain\Products\Services\Impl\ProductService;
use App\Domain\Products\Services\ProductServiceInterface;
use App\Domain\Products\Validators\Impl\ProductDataValidator;
use App\Domain\Products\Validators\ProductDataValidatorInterface;
use App\Infrastructure\Common\Database\DoctrineEntityManagerInterface;
use DI\ContainerBuilder;

final class ProductServiceDefinition implements ServiceDefinitionInterface
{
    public function register(ContainerBuilder $builder): void
    {
        // Product Repository
        $builder->addDefinitions([
            ProductRepositoryInterface::class => function ($container) {
                $doctrineManager = $container->get(DoctrineEntityManagerInterface::class);
                return new ProductRepository($doctrineManager->getMaster());
            },
        ]);

        // Product Data Validator
        $builder->addDefinitions([
            ProductDataValidatorInterface::class => function (): ProductDataValidatorInterface {
                return new ProductDataValidator();
            },
        ]);

        // Product Service
        $builder->addDefinitions([
            ProductServiceInterface::class => function ($container) {
                $repository = $container->get(ProductRepositoryInterface::class);
                $validator = $container->get(ProductDataValidatorInterface::class);
                return new ProductService($repository, $validator);
            },
        ]);
    }
}
