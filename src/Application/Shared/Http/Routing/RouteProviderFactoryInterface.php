<?php

declare(strict_types=1);

namespace App\Application\Shared\Http\Routing;

interface RouteProviderFactoryInterface
{
    /**
     * Cria RouteProvider para o módulo Core/Common (rotas básicas)
     */
    public function createCoreRouteProvider(): RouteProviderInterface;

    /**
     * Cria RouteProvider para o módulo System
     */
    public function createSystemRouteProvider(): RouteProviderInterface;

    /**
     * Cria RouteProvider para o módulo Auth
     */
    public function createAuthRouteProvider(): RouteProviderInterface;

    /**
     * Cria RouteProvider para o módulo Security
     */
    public function createSecurityRouteProvider(): RouteProviderInterface;
}
