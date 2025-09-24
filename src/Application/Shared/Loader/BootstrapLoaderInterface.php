<?php

declare(strict_types=1);

namespace App\Application\Shared\Loader;

use App\Application\Shared\Registry\BootstrapRegistryInterface;
use DI\ContainerBuilder;

interface BootstrapLoaderInterface
{
    /**
     * Carrega todos os bootstraps do registry no ContainerBuilder
     * Os bootstraps são carregados em ordem de prioridade
     */
    public function loadAll(BootstrapRegistryInterface $registry, ContainerBuilder $builder): void;
}
