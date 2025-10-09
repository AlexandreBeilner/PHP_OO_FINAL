<?php

declare(strict_types=1);

namespace App\Application\Modules\Financial\Bootstrap\Impl;

use App\Application\Modules\Financial\EntityPaths\Impl\FinancialEntityPathProvider;
use App\Application\Modules\Products\Http\Routing\Impl\ProductsRouteProvider;
use App\Application\Modules\System\Bootstrap\Impl\SystemBootstrap;
use App\Application\Shared\BootstrapInterface;
use App\Application\Shared\EntityPaths\EntityPathProviderInterface;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use App\Application\Shared\Impl\AbstractBootstrap;
use App\Application\Shared\Impl\CommonBootstrap;
use DI\ContainerBuilder;

final class FinancialBootstrap extends AbstractBootstrap implements BootstrapInterface
{
    public function belongsToModule(string $moduleName): bool
    {
        return $moduleName === 'Financial';
    }

    public function getEntityPathProvider(): ?EntityPathProviderInterface
    {
        return $this->createFinancialEntityPathProvider();
    }

    public function getModuleName(): string
    {
        return 'Financial';
    }

    public function getPriority(): int
    {
        return 50;
    }

    public function getRouteProvider(): ?RouteProviderInterface
    {
        return new ProductsRouteProvider();
    }

    public function hasEntityPathProvider(): bool
    {
        return true;
    }

    public function hasPriorityOver(BootstrapInterface $other): bool
    {
        if ($other instanceof CommonBootstrap) {
            return false;
        }
        if ($other instanceof SystemBootstrap) {
            return false;
        }
        return $this->getPriority() < $other->getPriority();
    }

    public function hasRoutes(): bool
    {
        return true;
    }

    public function register(ContainerBuilder $builder): void
    {
        $this->loadServiceDefinitions($builder, [
            FinancialServiceDefinition::class,
            FinancialValidationServiceDefinition::class,
            FinancialControllerDefinition::class,
        ]);
    }

    private function createFinancialEntityPathProvider(): FinancialEntityPathProvider
    {
        return new FinancialEntityPathProvider();
    }
}
