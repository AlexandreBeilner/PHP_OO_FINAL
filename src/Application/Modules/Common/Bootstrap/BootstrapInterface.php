<?php

declare(strict_types=1);

namespace App\Application\Modules\Common\Bootstrap;

use DI\ContainerBuilder;

interface BootstrapInterface
{
    /**
     * Registra as definições de serviços do módulo no container DI
     */
    public function register(ContainerBuilder $builder): void;

    /**
     * Retorna o nome do módulo
     */
    public function getModuleName(): string;

    /**
     * Retorna a prioridade de carregamento (menor número = maior prioridade)
     */
    public function getPriority(): int;
}
