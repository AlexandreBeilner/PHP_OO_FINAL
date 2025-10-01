<?php

declare(strict_types=1);

namespace App\Application\Shared\Orchestrator\Impl;

use App\Application\Modules\Products\Bootstrap\Impl\ProductsBootstrap;
use App\Application\Shared\Orchestrator\BootstrapOrchestratorInterface;
use App\Application\Shared\Orchestrator\LoaderBundle;
use App\Application\Shared\Registry\BootstrapRegistryInterface;
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
    private LoaderBundle $loaderBundle;

    /**
     * DI: Injeta dependências via construtor
     * Object Calisthenics: Máximo 2 variáveis de instância
     */
    public function __construct(
        BootstrapRegistryInterface $registry,
        LoaderBundle $loaderBundle
    ) {
        $this->registry = $registry;
        $this->loaderBundle = $loaderBundle;
    }

    public function registerBootstrap(BootstrapInterface $bootstrap): void
    {
        $this->registry->register($bootstrap);
    }

    public function findBootstrapByModule(string $moduleName): ?BootstrapInterface
    {
        return $this->registry->findByModule($moduleName);
    }

    /**
     * Tell Don't Ask: Carrega todos os serviços usando o bootstrap loader
     * Object Calisthenics: Delegação através do LoaderBundle
     */
    public function loadAllServices(ContainerBuilder $builder): void
    {
        $this->loaderBundle->getBootstrapLoader()->loadAll($this->registry, $builder);
    }

    /**
     * Tell Don't Ask: Carrega todas as rotas usando o route loader
     * Object Calisthenics: Delegação através do LoaderBundle
     */
    public function loadAllRoutes(App $app): void
    {
        $this->loaderBundle->getRouteLoader()->loadAllRoutes($this->registry, $app);
    }

    public function initializeDefaultBootstraps(): void
    {
        $this->registerBootstrap(new CommonBootstrap());
        $this->registerBootstrap(new SystemBootstrap());
        $this->registerBootstrap(new AuthBootstrap());
        $this->registerBootstrap(new SecurityBootstrap());
        $this->registerBootstrap(new ApplicationBootstrap());
        $this->registerBootstrap(new ProductsBootstrap());
    }

    /**
     * SRP: Coleta entity paths de todos os bootstraps
     * DIP: Usa EntityPathCollectorInterface abstraído
     * Object Calisthenics: Delega para métodos privados, um nível de indentação
     */
    public function collectAllEntityPaths(): array
    {
        $collector = $this->loaderBundle->getEntityPathCollector();
        $this->registerAllEntityPathProviders($collector);

        return $collector->collectAllEntityPaths();
    }

    /**
     * SRP: Registra todos os entity path providers no coletor
     * Object Calisthenics: Um nível de indentação, não usar else
     */
    private function registerAllEntityPathProviders($collector): void
    {
        foreach ($this->registry->getAll() as $bootstrap) {
            $this->registerProviderIfAvailable($bootstrap, $collector);
        }
    }

    /**
     * SRP: Registra provider se bootstrap tiver um
     * Object Calisthenics: Não usar else, guard clause
     */
    private function registerProviderIfAvailable(BootstrapInterface $bootstrap, $collector): void
    {
        if (!$bootstrap->hasEntityPathProvider()) {
            return;
        }

        $provider = $bootstrap->getEntityPathProvider();
        if ($provider !== null) {
            $collector->registerProvider($provider);
        }
    }
}
