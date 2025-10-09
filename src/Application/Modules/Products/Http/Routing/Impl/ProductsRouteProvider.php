<?php

declare(strict_types=1);

namespace App\Application\Modules\Products\Http\Routing\Impl;

use App\Application\Modules\Products\Controllers\Impl\ProductController;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use Slim\App;

/**
 * Provedor de rotas do módulo Products
 *
 * Define todas as rotas relacionadas ao CRUD de produtos:
 * - Rotas de produtos (CRUD)
 */
final class ProductsRouteProvider implements RouteProviderInterface
{
    public function belongsToModule(string $moduleName): bool
    {
        return $moduleName === 'Products';
    }

    public function getModuleName(): string
    {
        return 'Products';
    }

    public function getPriority(): int
    {
        return 50; // Mesma prioridade do ProductsBootstrap
    }

    public function getRoutePrefix(): string
    {
        return '/api/products';
    }

    public function hasPriorityOver(RouteProviderInterface $other): bool
    {
        // Products tem prioridade 50 (após System=30)
        return $this->getPriority() > $other->getPriority();
    }

    public function registerRoutes(App $app): void
    {
        // Rotas de produtos (CRUD)
        $app->group('/api/products', function ($group) {
            $group->get('', [ProductController::class, 'index']);
            $group->get('/{id:[0-9]+}', [ProductController::class, 'show']);
            $group->post('', [ProductController::class, 'create']);
            $group->put('/{id:[0-9]+}', [ProductController::class, 'update']);
            $group->delete('/{id:[0-9]+}', [ProductController::class, 'delete']);
        });
    }
}
