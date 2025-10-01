<?php

declare(strict_types=1);

namespace App\Infrastructure\Common\Database\Impl;

use App\Infrastructure\Common\Database\DoctrineEntityManagerFactoryInterface;
use App\Infrastructure\Common\Database\DoctrineEntityManagerInterface;
use App\Application\Shared\Utils\Impl\ProjectRootDiscovery;
use App\Application\Shared\Orchestrator\BootstrapOrchestratorInterface;
use PDO;

final class DoctrineEntityManagerFactory implements DoctrineEntityManagerFactoryInterface
{
    /**
     * Factory Method: Cria EntityManager com configuração dinâmica
     * DIP: Aceita orchestrator como dependência para coletar entity paths
     */
    public static function create(array $connectionConfig, ?BootstrapOrchestratorInterface $orchestrator = null): DoctrineEntityManagerInterface
    {
        $doctrineConfig = self::buildDoctrineConfig($connectionConfig, $orchestrator);
        return new DoctrineEntityManager($doctrineConfig);
    }

    private static function buildDbalConfig(array $connectionData): array
    {
        return [
            'driver' => 'pdo_pgsql',
            'host' => $connectionData['host'],
            'port' => $connectionData['port'],
            'dbname' => $connectionData['dbname'],
            'user' => $connectionData['user'],
            'password' => $connectionData['password'],
            'charset' => 'utf8',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        ];
    }

    /**
     * SRP: Constrói configuração do Doctrine
     * DIP: Usa orchestrator injetado para coletar entity paths dinamicamente
     * Object Calisthenics: Delegação para métodos privados
     */
    private static function buildDoctrineConfig(array $connectionConfig, ?BootstrapOrchestratorInterface $orchestrator = null): array
    {
        return [
            'doctrine' => [
                'dbal' => [
                    'connections' => [
                        'default' => self::buildDbalConfig($connectionConfig['connections']['master']),
                        'slave' => self::buildDbalConfig($connectionConfig['connections']['slave']),
                    ],
                ],
                'orm' => [
                    'auto_generate_proxy_classes' => true,
                    'proxy_dir' => __DIR__ . '/../../cache/proxies',
                    'proxy_namespace' => 'App\\Proxies',
                    'entity_paths' => self::resolveEntityPaths($orchestrator),
                    'mapping_types' => [
                        'enum' => 'string',
                    ],
                    'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                    'auto_mapping' => true,
                ],
            ],
        ];
    }

    /**
     * SRP: Resolve entity paths dinamicamente APENAS dos bootstraps
     * DIP: Depende completamente da abstração (orchestrator)
     * Object Calisthenics: Não usar else, guard clauses
     */
    private static function resolveEntityPaths(?BootstrapOrchestratorInterface $orchestrator): array
    {
        if ($orchestrator === null) {
            return [];
        }
        
        return $orchestrator->collectAllEntityPaths();
    }
}
