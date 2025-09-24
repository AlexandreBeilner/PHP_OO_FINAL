<?php

declare(strict_types=1);

namespace App\Application\Modules\Products\Http\Routing;

use App\Application\Shared\Http\Routing\RouteProviderInterface;
use Slim\App;

final class ProductsRouteProvider implements RouteProviderInterface
{

    public function registerRoutes(App $app): void
    {
        $app->group('/api/products', function ($group) {

        });
    }

    public function getRoutePrefix(): string
    {
        return '/api/products';
    }

    public function getModuleName(): string
    {
        return 'Products';
    }

    public function belongsToModule(string $moduleName): bool
    {
        return $moduleName === 'Products';
    }

    public function getPriority(): int
    {
        return 50;//o valor de prioridadde é referente a ordem de carregamento;
    }

    public function hasPriorityOver(RouteProviderInterface $other): bool
    {
        return false;
    }
}
