<?php

declare(strict_types=1);

namespace App\Application\Shared\Impl;

use App\Application\Shared\ServiceDefinitionInterface;
use App\Application\Shared\EntityPaths\EntityPathCollectorInterface;
use App\Application\Shared\EntityPaths\Impl\EntityPathCollector;
use DI\ContainerBuilder;

/**
 * Definição de serviços para componentes de Entity Paths
 * 
 * SRP: Responsabilidade única de registrar serviços de entity paths
 * DI: Configuração de injeção de dependências
 */
final class EntityPathServiceDefinition implements ServiceDefinitionInterface
{
    /**
     * Tell Don't Ask: Registra definições no container
     * SRP: Responsabilidade única de configurar DI
     */
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            // EntityPathCollector - Strategy Pattern para coleta de paths
            EntityPathCollectorInterface::class => function (): EntityPathCollectorInterface {
                return new EntityPathCollector();
            },
        ]);
    }
}
