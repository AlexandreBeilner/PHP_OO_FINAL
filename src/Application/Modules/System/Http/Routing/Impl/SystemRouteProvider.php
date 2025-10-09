<?php

declare(strict_types=1);

namespace App\Application\Modules\System\Http\Routing\Impl;

use App\Application\Modules\System\Controllers\Impl\SystemController;
use App\Application\Shared\Http\Routing\Impl\CoreRouteProvider;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use Slim\App;

/**
 * Provedor de rotas do módulo System
 *
 * Define todas as rotas relacionadas ao sistema:
 * - Informações do sistema
 * - Status de extensões
 * - Testes de conectividade
 */
final class SystemRouteProvider implements RouteProviderInterface
{
    public function belongsToModule(string $moduleName): bool
    {
        return $moduleName === 'System';
    }

    public function getModuleName(): string
    {
        return 'System';
    }

    public function getPriority(): int
    {
        return 20; // Mesma prioridade do SystemBootstrap
    }

    public function getRoutePrefix(): string
    {
        return '/api/system';
    }

    public function hasPriorityOver(RouteProviderInterface $other): bool
    {
        // System tem prioridade 20 - tem prioridade sobre Auth (25), Security (30)
        // Perde apenas para Core (10)
        if ($other instanceof CoreRouteProvider) {
            return false; // Core (10) tem prioridade maior
        }
        return true; // Tem prioridade sobre Auth (25), Security (30) e outros
    }

    public function registerRoutes(App $app): void
    {
        // Rotas do sistema
        $app->group('/api/system', function ($group) {
            $group->get('/info', [SystemController::class, 'getSystemInfo']);
            $group->get('/extensions-status', [SystemController::class, 'getRequiredExtensionsStatus']);
            $group->get('/doctrine-test', [SystemController::class, 'testDoctrine']);
        });
    }
}
