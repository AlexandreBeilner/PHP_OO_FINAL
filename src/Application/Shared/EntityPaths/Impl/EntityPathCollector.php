<?php

declare(strict_types=1);

namespace App\Application\Shared\EntityPaths\Impl;

use App\Application\Shared\EntityPaths\EntityPathCollectorInterface;
use App\Application\Shared\EntityPaths\EntityPathProviderInterface;
use App\Application\Shared\EntityPaths\EntityPaths;

/**
 * Implementação concreta do coletor de entity paths
 *
 * SRP: Responsabilidade única de coletar paths de providers
 * OCP: Aberto para extensão (novos providers), fechado para modificação
 * Object Calisthenics: Máximo 2 variáveis de instância, Tell Don't Ask
 */
final class EntityPathCollector implements EntityPathCollectorInterface
{
    private array $providers;

    /**
     * Object Calisthenics: Construtor sem parâmetros, inicializa estado
     */
    public function __construct()
    {
        $this->providers = [];
    }

    /**
     * SRP: Coleta paths de todos os providers
     * Object Calisthenics: Não usar else, um nível de indentação
     */
    public function collectAllEntityPaths(): array
    {
        $allPaths = [];

        foreach ($this->providers as $provider) {
            $allPaths = $this->mergeProviderPaths($provider, $allPaths);
        }

        return $this->createEntityPaths($allPaths)->toArray();
    }

    /**
     * Tell Don't Ask: Informa para registrar provider
     * Object Calisthenics: Um nível de indentação
     */
    public function registerProvider(EntityPathProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * Factory Method: Cria Value Object EntityPaths
     * SRP: Responsabilidade única de criar EntityPaths
     */
    private function createEntityPaths(array $paths): EntityPaths
    {
        return new EntityPaths($paths);
    }

    /**
     * SRP: Combina paths de um provider específico
     * Object Calisthenics: Um nível de indentação, não usar else
     */
    private function mergeProviderPaths(EntityPathProviderInterface $provider, array $currentPaths): array
    {
        if (! $provider->hasEntityPaths()) {
            return $currentPaths;
        }

        return array_merge($currentPaths, $provider->getEntityPaths());
    }
}
