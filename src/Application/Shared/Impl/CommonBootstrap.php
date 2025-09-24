<?php

declare(strict_types=1);

namespace App\Application\Shared\Impl;

use App\Application\Shared\BootstrapInterface;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use App\Application\Shared\Http\Routing\RouteProviderFactoryInterface;
use App\Application\Shared\Http\Routing\CoreRouteProvider;
use DI\ContainerBuilder;

final class CommonBootstrap extends AbstractBootstrap implements BootstrapInterface
{
    private ?RouteProviderFactoryInterface $routeProviderFactory;

    public function __construct(?RouteProviderFactoryInterface $routeProviderFactory = null)
    {
        $this->routeProviderFactory = $routeProviderFactory;
    }
    public function register(ContainerBuilder $builder): void
    {
        $definitions = [
            // Database
            \App\Application\Shared\Impl\DoctrineServiceDefinition::class,
            
            // Migrations (usando EntityManager existente)
            \App\Application\Shared\Impl\MigrationsServiceDefinition::class,
            
            // Common Services
            \App\Application\Shared\Impl\CommonServicesDefinition::class,
            
            // RouteProvider Factory para DIP
            \App\Application\Shared\Impl\RouteProviderFactoryDefinition::class,
        ];

        $this->loadServiceDefinitions($builder, $definitions);
    }

    public function getModuleName(): string
    {
        return 'Common';
    }

    public function belongsToModule(string $moduleName): bool
    {
        return $moduleName === 'Common';
    }

    public function getPriority(): int
    {
        return 10; // Alta prioridade - serviços base
    }

    public function hasPriorityOver(BootstrapInterface $other): bool
    {
        // Common tem prioridade 10 (alta) - só perde para prioridades menores que 10
        // Como não há nenhuma menor que 10, Common sempre tem prioridade
        return true;
    }

    public function hasRoutes(): bool
    {
        return true;
    }

    public function getRouteProvider(): ?RouteProviderInterface
    {
        if ($this->routeProviderFactory !== null) {
            return $this->routeProviderFactory->createCoreRouteProvider();
        }
        
        // Fallback para compatibilidade (viola DIP, mas mantém funcionalidade)
        return new CoreRouteProvider();
    }
}
