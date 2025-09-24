<?php

declare(strict_types=1);

namespace App\Application\Shared\Http\Routing;

use Slim\App;

/**
 * Interface para provedores de rotas modulares
 * 
 * Cada módulo deve implementar esta interface para registrar suas rotas específicas
 * Segue o princípio de responsabilidade única - cada módulo gerencia suas próprias rotas
 */
interface RouteProviderInterface
{
    /**
     * Registra as rotas do módulo na aplicação Slim
     *
     * @param App $app Instância da aplicação Slim
     */
    public function registerRoutes(App $app): void;

    /**
     * Retorna o prefixo das rotas do módulo (ex: '/api/security')
     *
     * @return string Prefixo das rotas
     */
    public function getRoutePrefix(): string;

    /**
     * Retorna o nome do módulo (interface externa)
     */
    public function getModuleName(): string;

    /**
     * Verifica se este provedor pertence ao módulo especificado
     */
    public function belongsToModule(string $moduleName): bool;

    /**
     * Retorna a prioridade de registro das rotas (interface externa)
     */
    public function getPriority(): int;

    /**
     * Verifica se este provedor tem prioridade maior que outro
     * 
     * @param RouteProviderInterface $other Provedor para comparar
     * @return bool true se este tem prioridade maior (menor número)
     */
    public function hasPriorityOver(RouteProviderInterface $other): bool;
}
