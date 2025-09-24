<?php

declare(strict_types=1);

namespace App\Application\Shared;

use App\Application\Shared\Http\Routing\RouteProviderInterface;
use DI\ContainerBuilder;

interface BootstrapInterface
{
    /**
     * Registra as definições de serviços do módulo no container DI
     */
    public function register(ContainerBuilder $builder): void;

    /**
     * Retorna o nome do módulo (interface externa)
     */
    public function getModuleName(): string;

    /**
     * Verifica se este bootstrap pertence ao módulo especificado
     */
    public function belongsToModule(string $moduleName): bool;

    /**
     * Retorna a prioridade de carregamento (interface externa)
     */
    public function getPriority(): int;

    /**
     * Verifica se este bootstrap tem prioridade maior que outro
     * 
     * @param BootstrapInterface $other Bootstrap para comparar
     * @return bool true se este tem prioridade maior (menor número)
     */
    public function hasPriorityOver(BootstrapInterface $other): bool;

    /**
     * Verifica se o módulo possui rotas específicas
     */
    public function hasRoutes(): bool;

    /**
     * Retorna o provedor de rotas (apenas se hasRoutes() for true)
     */
    public function getRouteProvider(): ?RouteProviderInterface;
}
