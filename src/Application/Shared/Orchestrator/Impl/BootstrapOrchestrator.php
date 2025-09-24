<?php

declare(strict_types=1);

namespace App\Application\Shared\Orchestrator\Impl;

use App\Application\Shared\Orchestrator\BootstrapOrchestratorInterface;
use App\Application\Shared\Registry\BootstrapRegistryInterface;
use App\Application\Shared\Loader\BootstrapLoaderInterface;
use App\Application\Shared\Loader\RouteLoaderInterface;
use App\Application\Shared\BootstrapInterface;
use App\Application\Shared\Impl\CommonBootstrap;
use App\Application\Shared\Impl\ApplicationBootstrap;
use App\Application\Modules\System\Bootstrap\Impl\SystemBootstrap;
use App\Application\Modules\Auth\Bootstrap\Impl\AuthBootstrap;
use App\Application\Modules\Security\Bootstrap\Impl\SecurityBootstrap;
use DI\ContainerBuilder;
use Slim\App;

final class BootstrapOrchestrator implements BootstrapOrchestratorInterface
{
    private BootstrapRegistryInterface $registry;
    private BootstrapLoaderInterface $bootstrapLoader;
    private RouteLoaderInterface $routeLoader;

    public function __construct(
        BootstrapRegistryInterface $registry,
        BootstrapLoaderInterface $bootstrapLoader,
        RouteLoaderInterface $routeLoader
    ) {
        $this->registry = $registry;
        $this->bootstrapLoader = $bootstrapLoader;
        $this->routeLoader = $routeLoader;
    }

    public function registerBootstrap(BootstrapInterface $bootstrap): void
    {
        $this->registry->register($bootstrap);
    }

    public function findBootstrapByModule(string $moduleName): ?BootstrapInterface
    {
        return $this->registry->findByModule($moduleName);
    }

    public function loadAllServices(ContainerBuilder $builder): void
    {
        $this->bootstrapLoader->loadAll($this->registry, $builder);
    }

    public function loadAllRoutes(App $app): void
    {
        $this->routeLoader->loadAllRoutes($this->registry, $app);
    }

    public function initializeDefaultBootstraps(): void
    {
        $this->registerBootstrap(new CommonBootstrap());
        $this->registerBootstrap(new SystemBootstrap());
        $this->registerBootstrap(new AuthBootstrap());
        $this->registerBootstrap(new SecurityBootstrap());
        $this->registerBootstrap(new ApplicationBootstrap());
    }
}
