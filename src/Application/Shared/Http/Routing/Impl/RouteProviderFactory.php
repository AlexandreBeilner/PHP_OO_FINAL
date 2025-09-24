<?php

declare(strict_types=1);

namespace App\Application\Shared\Http\Routing\Impl;

use App\Application\Modules\Products\Http\Routing\ProductsRouteProvider;
use App\Application\Shared\Http\Routing\RouteProviderFactoryInterface;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use App\Application\Shared\Http\Routing\CoreRouteProvider;
use App\Application\Modules\System\Http\Routing\SystemRouteProvider;
use App\Application\Modules\Auth\Http\Routing\AuthRouteProvider;
use App\Application\Modules\Security\Http\Routing\SecurityRouteProvider;

final class RouteProviderFactory implements RouteProviderFactoryInterface
{
    public function createCoreRouteProvider(): RouteProviderInterface
    {
        return new CoreRouteProvider();
    }

    public function createSystemRouteProvider(): RouteProviderInterface
    {
        return new SystemRouteProvider();
    }

    public function createAuthRouteProvider(): RouteProviderInterface
    {
        return new AuthRouteProvider();
    }

    public function createSecurityRouteProvider(): RouteProviderInterface
    {
        return new SecurityRouteProvider();
    }

    public function createProductsRouteProvider(): RouteProviderInterface
    {
        return new ProductsRouteProvider();
    }
}
