<?php

declare(strict_types=1);

namespace App\Application\Shared\Registry;

use App\Application\Shared\BootstrapInterface;

interface BootstrapRegistryInterface
{
    /**
     * Busca um bootstrap pelo nome do módulo
     */
    public function findByModule(string $moduleName): ?BootstrapInterface;

    /**
     * Retorna todos os bootstraps registrados
     *
     * @return BootstrapInterface[]
     */
    public function getAll(): array;

    /**
     * Retorna todos os bootstraps ordenados por prioridade
     *
     * @return BootstrapInterface[]
     */
    public function getAllSortedByPriority(): array;

    /**
     * Registra um bootstrap no registry
     */
    public function register(BootstrapInterface $bootstrap): void;
}
