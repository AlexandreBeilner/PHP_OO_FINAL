<?php

declare(strict_types=1);

namespace App\Application\Shared\Orchestrator;

use App\Application\Shared\EntityPaths\EntityPathCollectorInterface;
use App\Application\Shared\Loader\BootstrapLoaderInterface;
use App\Application\Shared\Loader\RouteLoaderInterface;

/**
 * Value Object que agrupa todos os loaders
 *
 * Object Calisthenics: Encapsula múltiplos objetos relacionados
 * SRP: Responsabilidade única de agrupar loaders
 */
final class LoaderBundle
{
    private BootstrapLoaderInterface $bootstrapLoader;
    private EntityPathCollectorInterface $entityPathCollector;
    private RouteLoaderInterface $routeLoader;

    /**
     * DI: Injeta todos os loaders via construtor
     * Object Calisthenics: Construtor focado na inicialização
     */
    public function __construct(
        BootstrapLoaderInterface $bootstrapLoader,
        RouteLoaderInterface $routeLoader,
        EntityPathCollectorInterface $entityPathCollector
    ) {
        $this->bootstrapLoader = $bootstrapLoader;
        $this->routeLoader = $routeLoader;
        $this->entityPathCollector = $entityPathCollector;
    }

    /**
     * Tell Don't Ask: Retorna bootstrap loader
     */
    public function getBootstrapLoader(): BootstrapLoaderInterface
    {
        return $this->bootstrapLoader;
    }

    /**
     * Tell Don't Ask: Retorna entity path collector
     */
    public function getEntityPathCollector(): EntityPathCollectorInterface
    {
        return $this->entityPathCollector;
    }

    /**
     * Tell Don't Ask: Retorna route loader
     */
    public function getRouteLoader(): RouteLoaderInterface
    {
        return $this->routeLoader;
    }
}
