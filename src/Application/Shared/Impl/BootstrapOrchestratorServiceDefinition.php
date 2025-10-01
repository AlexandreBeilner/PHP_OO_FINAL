<?php

declare(strict_types=1);

namespace App\Application\Shared\Impl;

use App\Application\Shared\ServiceDefinitionInterface;
use App\Application\Shared\Orchestrator\BootstrapOrchestratorInterface;
use App\Application\Shared\Orchestrator\Impl\BootstrapOrchestrator;
use App\Application\Shared\Orchestrator\LoaderBundle;
use App\Application\Shared\Registry\BootstrapRegistryInterface;
use App\Application\Shared\Registry\Impl\BootstrapRegistry;
use App\Application\Shared\Loader\BootstrapLoaderInterface;
use App\Application\Shared\Loader\Impl\BootstrapLoader;
use App\Application\Shared\Loader\RouteLoaderInterface;
use App\Application\Shared\Loader\Impl\RouteLoader;
use App\Application\Shared\EntityPaths\EntityPathCollectorInterface;
use DI\ContainerBuilder;

/**
 * Definição de serviços para BootstrapOrchestrator
 * 
 * SRP: Responsabilidade única de configurar BootstrapOrchestrator
 * DI: Configura todas as dependências necessárias
 */
final class BootstrapOrchestratorServiceDefinition implements ServiceDefinitionInterface
{
    /**
     * Tell Don't Ask: Registra definições do orchestrator
     * SRP: Configuração focada no orchestrator e suas dependências
     */
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            // Registry para bootstraps
            BootstrapRegistryInterface::class => function (): BootstrapRegistryInterface {
                return new BootstrapRegistry();
            },
            
            // Loaders individuais
            BootstrapLoaderInterface::class => function (): BootstrapLoaderInterface {
                return new BootstrapLoader();
            },
            
            RouteLoaderInterface::class => function (): RouteLoaderInterface {
                return new RouteLoader();
            },
            
            // LoaderBundle - Object Calisthenics pattern
            LoaderBundle::class => function ($container): LoaderBundle {
                return new LoaderBundle(
                    $container->get(BootstrapLoaderInterface::class),
                    $container->get(RouteLoaderInterface::class),
                    $container->get(EntityPathCollectorInterface::class)
                );
            },
            
            // BootstrapOrchestrator principal
            BootstrapOrchestratorInterface::class => function ($container): BootstrapOrchestratorInterface {
                return new BootstrapOrchestrator(
                    $container->get(BootstrapRegistryInterface::class),
                    $container->get(LoaderBundle::class)
                );
            },
        ]);
    }
}
