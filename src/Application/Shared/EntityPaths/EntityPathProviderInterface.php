<?php

declare(strict_types=1);

namespace App\Application\Shared\EntityPaths;

/**
 * Contrato para provedores de caminhos de entidades Doctrine
 *
 * SRP: Responsabilidade única de fornecer paths de entidades
 * ISP: Interface segregada e focada apenas em entity paths
 */
interface EntityPathProviderInterface
{
    /**
     * Retorna os caminhos das entidades deste provedor
     *
     * @return string[] Array de paths absolutos para diretórios de entidades
     */
    public function getEntityPaths(): array;

    /**
     * Verifica se este provedor possui entidades para registrar
     */
    public function hasEntityPaths(): bool;
}
