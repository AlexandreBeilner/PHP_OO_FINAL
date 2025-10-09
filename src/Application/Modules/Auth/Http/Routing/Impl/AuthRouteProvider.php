<?php

declare(strict_types=1);

namespace App\Application\Modules\Auth\Http\Routing\Impl;

use App\Application\Modules\Auth\Controllers\AuthControllerInterface;
use App\Application\Modules\System\Http\Routing\Impl\SystemRouteProvider;
use App\Application\Shared\Http\Routing\Impl\CoreRouteProvider;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use Slim\App;

/**
 * Provedor de rotas do módulo Auth
 *
 * Define todas as rotas relacionadas à autenticação:
 * - Login
 * - Mudança de senha
 * - Ativação/Desativação de usuários
 */
final class AuthRouteProvider implements RouteProviderInterface
{
    public function belongsToModule(string $moduleName): bool
    {
        return $moduleName === 'Auth';
    }

    public function getModuleName(): string
    {
        return 'Auth';
    }

    public function getPriority(): int
    {
        return 25; // Prioridade entre System (20) e Security (30)
    }

    public function getRoutePrefix(): string
    {
        return '/api/auth';
    }

    public function hasPriorityOver(RouteProviderInterface $other): bool
    {
        // Auth tem prioridade 25
        if ($other instanceof CoreRouteProvider) {
            return false; // Core (10) tem prioridade maior
        }
        if ($other instanceof SystemRouteProvider) {
            return false; // System (20) tem prioridade maior
        }
        return true; // Tem prioridade sobre Security (30) e outros
    }

    public function registerRoutes(App $app): void
    {
        // Rotas de autenticação
        $app->group('/api/auth', function ($group) {
            $group->post('/login', [AuthControllerInterface::class, 'login']);
            $group->post('/change-password', [AuthControllerInterface::class, 'changePassword']);
            $group->post('/activate/{id:[0-9]+}', [AuthControllerInterface::class, 'activateUser']);
            $group->post('/deactivate/{id:[0-9]+}', [AuthControllerInterface::class, 'deactivateUser']);
        });
    }
}
