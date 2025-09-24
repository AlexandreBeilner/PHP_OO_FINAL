<?php

declare(strict_types=1);

namespace App\Application\Shared\Loader\Impl;

use App\Application\Shared\Loader\RouteLoaderInterface;
use App\Application\Shared\Registry\BootstrapRegistryInterface;
use App\Application\Shared\Http\Routing\RouteProviderManager;
use Slim\App;

final class RouteLoader implements RouteLoaderInterface
{
    public function loadAllRoutes(BootstrapRegistryInterface $registry, App $app): void
    {
        $routeProviderManager = new RouteProviderManager();
        
        $bootstraps = $registry->getAll();
        foreach ($bootstraps as $bootstrap) {
            $routeProvider = $bootstrap->getRouteProvider();
            if ($routeProvider !== null) {
                $routeProviderManager->registerRouteProvider($routeProvider);
            }
        }
        
        $routeProviderManager->loadAllRoutes($app);
    }
}
