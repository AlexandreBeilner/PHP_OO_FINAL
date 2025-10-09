<?php

namespace App\Application\Modules\Financial\Http\Routing\Impl;

use App\Application\Modules\Financial\Controllers\Impl\BankAccountController;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use Slim\App;

class FinancialRouteProvider implements RouteProviderInterface
{

    public function belongsToModule(string $moduleName): bool
    {
        return $moduleName === 'Financial';
    }

    public function getModuleName(): string
    {
        return 'Financial';
    }

    public function getPriority(): int
    {
        return 50;
    }

    public function getRoutePrefix(): string
    {
        return '/api/financial';
    }

    public function hasPriorityOver(RouteProviderInterface $other): bool
    {
        return $this->getPriority() > $other->getPriority();
    }

    public function registerRoutes(App $app): void
    {
        $app->group('/api/financial/bank-account', function ($group) {
            $group->get('', [BankAccountController::class, 'index']);
            $group->get('/{id:[0-9]+}', [BankAccountController::class, 'show']);
            $group->post('', [BankAccountController::class, 'create']);
            $group->put('/{id:[0-9]+}', [BankAccountController::class, 'update']);
            $group->delete('/{id:[0-9]+}', [BankAccountController::class, 'delete']);
        });
    }
}