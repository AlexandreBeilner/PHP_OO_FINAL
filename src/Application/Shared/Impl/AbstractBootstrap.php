<?php

declare(strict_types=1);

namespace App\Application\Shared\Impl;

use App\Application\Shared\BootstrapInterface;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use DI\ContainerBuilder;

abstract class AbstractBootstrap implements BootstrapInterface
{
    protected function loadServiceDefinitions(ContainerBuilder $builder, array $definitions): void
    {
        foreach ($definitions as $definition) {
            if (is_string($definition) && class_exists($definition)) {
                $instance = new $definition();
                if (method_exists($instance, 'register')) {
                    $instance->register($builder);
                }
            }
        }
    }

    public function getPriority(): int
    {
        return 100; // Prioridade padrão
    }

    /**
     * Implementação Tell Don't Ask - verifica se pertence ao módulo
     * Classes filhas devem sobrescrever com lógica direta
     */
    public function belongsToModule(string $moduleName): bool
    {
        // Implementação padrão - classes filhas devem sobrescrever
        return false;
    }

    /**
     * Implementação Tell Don't Ask - verifica prioridade maior
     * Menor número = maior prioridade
     * Classes filhas devem sobrescrever com lógica direta
     */
    public function hasPriorityOver(BootstrapInterface $other): bool
    {
        // Implementação padrão - classes filhas devem sobrescrever
        // Prioridade padrão é 100 (muito baixa)
        return false; // Qualquer outra classe tem prioridade maior
    }

    /**
     * Implementação padrão - módulos sem rotas retornam false
     */
    public function hasRoutes(): bool
    {
        return false;
    }

    /**
     * Implementação padrão - módulos sem rotas retornam null
     */
    public function getRouteProvider(): ?RouteProviderInterface
    {
        return null;
    }
}
