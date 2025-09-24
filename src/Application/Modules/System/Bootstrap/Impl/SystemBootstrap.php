<?php

declare(strict_types=1);

namespace App\Application\Modules\System\Bootstrap\Impl;

use App\Application\Shared\BootstrapInterface;
use App\Application\Shared\Impl\AbstractBootstrap;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use App\Application\Shared\Http\Routing\RouteProviderFactoryInterface;
use App\Application\Modules\System\Http\Routing\SystemRouteProvider;
use DI\ContainerBuilder;

final class SystemBootstrap extends AbstractBootstrap implements BootstrapInterface
{
    private ?RouteProviderFactoryInterface $routeProviderFactory;

    public function __construct(?RouteProviderFactoryInterface $routeProviderFactory = null)
    {
        $this->routeProviderFactory = $routeProviderFactory;
    }
    public function register(ContainerBuilder $builder): void
    {
        $definitions = [
            // System Services
            \App\Application\Modules\System\Bootstrap\Impl\SystemServiceDefinition::class,
            \App\Application\Modules\System\Bootstrap\Impl\SystemResponseServiceDefinition::class,
            
            // System Controllers
            \App\Application\Modules\System\Bootstrap\Impl\SystemControllerDefinition::class,
        ];

        $this->loadServiceDefinitions($builder, $definitions);
    }

    public function getModuleName(): string
    {
        return 'System';
    }

    public function belongsToModule(string $moduleName): bool
    {
        return $moduleName === 'System';
    }

    public function getPriority(): int
    {
        return 20; // Prioridade média
    }

    public function hasPriorityOver(BootstrapInterface $other): bool
    {
        // System tem prioridade 20 - tem prioridade sobre 25, 30, 50, 100+
        // Perde apenas para Common (10)
        if ($other instanceof \App\Application\Shared\Impl\CommonBootstrap) {
            return false; // Common (10) tem prioridade maior
        }
        return true; // Tem prioridade sobre todos os outros
    }

    public function hasRoutes(): bool
    {
        return true;
    }

    public function getRouteProvider(): ?RouteProviderInterface
    {
        if ($this->routeProviderFactory !== null) {
            return $this->routeProviderFactory->createSystemRouteProvider();
        }
        
        // Fallback para compatibilidade (viola DIP, mas mantém funcionalidade)
        return new SystemRouteProvider();
    }
}
