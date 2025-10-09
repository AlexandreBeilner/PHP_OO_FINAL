<?php

declare(strict_types=1);

namespace App\Application\Shared\EntityPaths;

/**
 * Contrato para coletores de caminhos de entidades
 *
 * SRP: Responsabilidade única de coletar paths de múltiplos providers
 * DIP: Dependência de abstração (EntityPathProviderInterface)
 */
interface EntityPathCollectorInterface
{
    /**
     * Coleta todos os entity paths de todos os providers registrados
     *
     * @return string[] Array único de paths (sem duplicatas)
     */
    public function collectAllEntityPaths(): array;

    /**
     * Registra um provedor de entity paths
     *
     * Tell Don't Ask: Informa ao coletor para registrar o provider
     */
    public function registerProvider(EntityPathProviderInterface $provider): void;
}
