<?php

namespace App\Application\Modules\Products\Bootstrap\Impl;

use App\Application\Modules\Common\Bootstrap\Impl\AbstractBootstrap;
use App\Application\Modules\Products\Http\Routing\ProductsRouteProvider;
use App\Application\Shared\BootstrapInterface;
use App\Application\Shared\Http\Routing\RouteProviderFactoryInterface;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use DI\ContainerBuilder;

final class ProductsBootstrap extends AbstractBootstrap implements BootstrapInterface
{
    private ?RouteProviderFactoryInterface $routeProviderFactory;

    public function __construct(?RouteProviderFactoryInterface $routeProviderFactory = null)
    {
        $this->routeProviderFactory = $routeProviderFactory;
    }

    public function register(ContainerBuilder $builder): void
    {
        $definitions = [];
        $this->loadServiceDefinitions($builder, $definitions);
    }

    public function getModuleName(): string
    {
        return 'Products';
    }

    public function belongsToModule(string $moduleName): bool
    {
        return $moduleName === 'Products';
    }

    public function hasPriorityOver(BootstrapInterface $other): bool
    {
        return false;
    }

    public function hasRoutes(): bool
    {
        return true;
    }

    public function getRouteProvider(): ?RouteProviderInterface
    {
        if ($this->routeProviderFactory !== null) {
            return $this->routeProviderFactory->createProductsRouteProvider();
        }

        return new ProductsRouteProvider();
    }
}
