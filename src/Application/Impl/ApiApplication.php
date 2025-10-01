<?php

declare(strict_types=1);

namespace App\Application\Impl;

use App\Application\ApplicationInterface;
use App\Application\Shared\Http\Impl\SlimAppFactory;
use App\Application\Shared\Http\SlimAppFactoryInterface;
use App\Application\Shared\Orchestrator\BootstrapOrchestratorInterface;
use App\Application\Shared\Orchestrator\Impl\BootstrapOrchestrator;
use App\Application\Shared\Orchestrator\LoaderBundle;
use App\Application\Shared\Registry\Impl\BootstrapRegistry;
use App\Application\Shared\Loader\Impl\BootstrapLoader;
use App\Application\Shared\Loader\Impl\RouteLoader;
use App\Application\Shared\EntityPaths\Impl\EntityPathCollector;
use DI\Container;
use DI\ContainerBuilder;
use Slim\App;

final class ApiApplication implements ApplicationInterface
{
    private Container $container;
    private static ?ApiApplication $instance = null;
    private ?SlimAppFactoryInterface $slimAppFactory = null;

    private function __construct()
    {
        $this->initializeContainer();
    }

    public function __wakeup()
    {
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function createSlimApp(): App
    {
        return $this->getSlimAppFactory()->create();
    }

    public static function getInstance(): ApplicationInterface
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getSlimAppFactory(): SlimAppFactoryInterface
    {
        if ($this->slimAppFactory === null) {
            $this->slimAppFactory = new SlimAppFactory($this);
        }

        return $this->slimAppFactory;
    }

    private function __clone()
    {
    }

    // Prevenir clonagem

    private function initializeContainer(): void
    {
        $builder = new ContainerBuilder();

        // Habilitar compilação com classe base correta
        $builder->enableCompilation(__DIR__ . '/../../../cache', 'CompiledContainer', Container::class);

        // Cache de definições desabilitado (APCu não disponível)
        // $builder->enableDefinitionCache();

        // Criar orchestrator temporário para carregar service definitions
        $orchestrator = $this->createTemporaryOrchestrator();
        $orchestrator->initializeDefaultBootstraps();
        $orchestrator->loadAllServices($builder);
        
        $this->container = $builder->build();
    }

    /**
     * Factory Method: Cria orchestrator temporário para carregar service definitions
     * SRP: Responsabilidade única de criar orchestrator configurado
     * Object Calisthenics: Método privado focado
     */
    private function createTemporaryOrchestrator(): BootstrapOrchestratorInterface
    {
        $loaderBundle = new LoaderBundle(
            new BootstrapLoader(),
            new RouteLoader(),
            new EntityPathCollector()
        );
        
        return new BootstrapOrchestrator(
            new BootstrapRegistry(),
            $loaderBundle
        );
    }

    // Prevenir unserialize
}
