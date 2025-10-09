<?php

declare(strict_types=1);

namespace App\Application\Modules\Products\Bootstrap\Impl;

use App\Application\Shared\ServiceDefinitionInterface;
use App\Domain\Products\Services\Impl\ProductValidationService;
use App\Domain\Products\Services\ProductValidationServiceInterface;
use App\Domain\Products\Validators\ProductDataValidatorInterface;
use DI\ContainerBuilder;

final class ProductValidationServiceDefinition implements ServiceDefinitionInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            ProductValidationServiceInterface::class => function ($container) {
                $productDataValidator = $container->get(ProductDataValidatorInterface::class);
                return new ProductValidationService($productDataValidator);
            },
        ]);
    }
}
