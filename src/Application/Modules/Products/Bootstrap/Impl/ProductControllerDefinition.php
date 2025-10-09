<?php

declare(strict_types=1);

namespace App\Application\Modules\Products\Bootstrap\Impl;

use App\Application\Modules\Products\Controllers\Impl\ProductController;
use App\Application\Modules\Products\Controllers\ProductControllerInterface;
use App\Application\Shared\ServiceDefinitionInterface;
use App\Domain\Products\Services\ProductServiceInterface;
use App\Domain\Products\Services\ProductValidationServiceInterface;
use DI\ContainerBuilder;

final class ProductControllerDefinition implements ServiceDefinitionInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            ProductControllerInterface::class => function ($container) {
                $productService = $container->get(ProductServiceInterface::class);
                $productValidationService = $container->get(ProductValidationServiceInterface::class);
                return new ProductController($productService, $productValidationService);
            },
        ]);
    }
}
