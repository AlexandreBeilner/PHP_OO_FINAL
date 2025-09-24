<?php

declare(strict_types=1);

namespace App\Infrastructure\Common\Database\Impl;

use App\Infrastructure\Common\Database\DoctrineEntityManagerFactoryInterface;
use App\Infrastructure\Common\Database\DoctrineEntityManagerInterface;
use PDO;

final class DoctrineEntityManagerFactory implements DoctrineEntityManagerFactoryInterface
{
    public static function create(array $connectionConfig): DoctrineEntityManagerInterface
    {
        $doctrineConfig = self::buildDoctrineConfig($connectionConfig);
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

    private static function buildDoctrineConfig(array $connectionConfig): array
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
                    'entity_paths' => [
                        __DIR__ . '/../../../Domain/*/Entities/Impl',
                        __DIR__ . '/../../../Common/Entities',
                    ],
                    'mapping_types' => [
                        'enum' => 'string',
                    ],
                    'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                    'auto_mapping' => true,
                ],
            ],
        ];
    }
}
