<?php

declare(strict_types=1);

namespace App\Application\Shared\Orchestrator;

use App\Application\Shared\BootstrapInterface;
use DI\ContainerBuilder;
use Slim\App;

interface BootstrapOrchestratorInterface
{
    /**
     * Registra um bootstrap no orquestrador
     */
    public function registerBootstrap(BootstrapInterface $bootstrap): void;

    /**
     * Busca um bootstrap pelo nome do módulo
     */
    public function findBootstrapByModule(string $moduleName): ?BootstrapInterface;

    /**
     * Carrega todos os bootstraps no ContainerBuilder
     */
    public function loadAllServices(ContainerBuilder $builder): void;

    /**
     * Carrega todas as rotas na aplicação Slim
     */
    public function loadAllRoutes(App $app): void;

    /**
     * Inicializa com bootstraps padrão do sistema
     */
    public function initializeDefaultBootstraps(): void;

    /**
     * Coleta todos os entity paths de todos os bootstraps registrados
     * 
     * @return string[] Array de paths das entidades para configuração do Doctrine
     */
    public function collectAllEntityPaths(): array;
}
