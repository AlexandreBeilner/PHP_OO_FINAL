<?php

declare(strict_types=1);

namespace App\Application\Shared\Http\Routing\Impl;

use App\Application\Modules\Auth\Http\Routing\Impl\AuthRouteProvider;
use App\Application\Modules\Security\Http\Routing\Impl\SecurityRouteProvider;
use App\Application\Modules\System\Http\Routing\Impl\SystemRouteProvider;
use App\Application\Shared\Http\Routing\RouteProviderFactoryInterface;
use App\Application\Shared\Http\Routing\RouteProviderInterface;

final class RouteProviderFactory implements RouteProviderFactoryInterface
{
    public function createAuthRouteProvider(): RouteProviderInterface
    {
        return new AuthRouteProvider();
    }

    public function createCoreRouteProvider(): RouteProviderInterface
    {
        return new CoreRouteProvider();
    }

    public function createSecurityRouteProvider(): RouteProviderInterface
    {
        return new SecurityRouteProvider();
    }

    public function createSystemRouteProvider(): RouteProviderInterface
    {
        return new SystemRouteProvider();
    }
}
