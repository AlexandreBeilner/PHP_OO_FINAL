<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\Http\Routing;

use App\Application\Shared\Http\Routing\RouteProviderInterface;
use App\Application\Modules\Security\Controllers\Impl\UserController;
use Slim\App;

/**
 * Provedor de rotas do módulo Security
 * 
 * Define todas as rotas relacionadas à segurança de usuários:
 * - Rotas de usuários (CRUD)
 * 
 * Nota: Rotas de autenticação foram movidas para o módulo Auth
 */
final class SecurityRouteProvider implements RouteProviderInterface
{
    public function registerRoutes(App $app): void
    {
        // Rotas de segurança (usuários CRUD)
        $app->group('/api/security', function ($group) {
            $group->get('/users', [UserController::class, 'index']);
            $group->get('/users/{id:[0-9]+}', [UserController::class, 'show']);
            $group->post('/users', [UserController::class, 'create']);
            $group->put('/users/{id:[0-9]+}', [UserController::class, 'update']);
            $group->delete('/users/{id:[0-9]+}', [UserController::class, 'delete']);
        });
    }

    public function getRoutePrefix(): string
    {
        return '/api/security';
    }

    public function getModuleName(): string
    {
        return 'Security';
    }

    public function belongsToModule(string $moduleName): bool
    {
        return $moduleName === 'Security';
    }

    public function getPriority(): int
    {
        return 30; // Mesma prioridade do SecurityBootstrap
    }

    public function hasPriorityOver(RouteProviderInterface $other): bool
    {
        // Security tem prioridade 30 (menor entre todos os módulos)
        // Só tem prioridade sobre classes que não implementam os métodos específicos
        return false; // Na prática, todos os outros têm prioridade maior
    }
}
