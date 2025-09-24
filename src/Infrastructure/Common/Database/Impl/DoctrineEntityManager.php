<?php

declare(strict_types=1);

namespace App\Infrastructure\Common\Database\Impl;

use App\Infrastructure\Common\Database\DoctrineEntityManagerInterface;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\ORMSetup;
use Exception;

final class DoctrineEntityManager implements DoctrineEntityManagerInterface
{
    private array $config;
    private EntityManagerInterface $master;
    private EntityManagerInterface $slave;

    public function __construct(array $doctrineConfig)
    {
        $this->config = $doctrineConfig['doctrine'];
        $this->initializeEntityManagers();
    }

    /**
     * Retorna estatísticas das conexões
     */
    public function getConnectionStats(): array
    {
        return [
            'master_connected' => $this->master->getConnection()->isConnected(),
            'slave_connected' => $this->slave->getConnection()->isConnected(),
            'master_driver' => $this->master->getConnection()->getDriver()->getDatabasePlatform()->getName(),
            'slave_driver' => $this->slave->getConnection()->getDriver()->getDatabasePlatform()->getName(),
        ];
    }

    /**
     * Retorna o EntityManager apropriado baseado na operação
     *
     * @param string $operation 'read' ou 'write'
     * @return EntityManagerInterface
     */
    public function getEntityManager(string $operation = 'read'): EntityManagerInterface
    {
        return $operation === 'write' ? $this->master : $this->slave;
    }

    /**
     * Retorna o EntityManager baseado no tipo de query
     *
     * @param string $queryType 'SELECT', 'INSERT', 'UPDATE', 'DELETE'
     * @return EntityManagerInterface
     */
    public function getEntityManagerForQuery(string $queryType): EntityManagerInterface
    {
        return strtoupper($queryType) === 'SELECT' ? $this->slave : $this->master;
    }

    public function getMaster(): EntityManagerInterface
    {
        return $this->master;
    }

    public function getSlave(): EntityManagerInterface
    {
        return $this->slave;
    }

    /**
     * Testa as conexões
     */
    public function testConnections(): array
    {
        $results = [];

        try {
            $masterResult = $this->master->getConnection()->fetchAssociative("SELECT 'Master DB' as db_type, version() as version");
            $results['master'] = [
                'status' => 'success',
                'data' => $masterResult,
            ];
        } catch (Exception $e) {
            $results['master'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        try {
            $slaveResult = $this->slave->getConnection()->fetchAssociative("SELECT 'Slave DB' as db_type, version() as version");
            $results['slave'] = [
                'status' => 'success',
                'data' => $slaveResult,
            ];
        } catch (Exception $e) {
            $results['slave'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return $results;
    }

    private function getEntityPaths(): array
    {
        $paths = [];
        $entityPaths = $this->config['orm']['entity_paths'];

        foreach ($entityPaths as $path) {
            if (is_dir($path)) {
                $paths[] = $path;
            } else {
                // Expandir glob patterns
                $expanded = glob($path);
                if ($expanded) {
                    $paths = array_merge($paths, $expanded);
                }
            }
        }

        // Caminhos das entidades configurados

        return $paths;
    }

    private function initializeEntityManagers(): void
    {
        // Configuração base do ORM (usando annotations para PHP 7.4)
        $ormConfig = ORMSetup::createAnnotationMetadataConfiguration(
            $this->getEntityPaths(),
            $this->config['orm']['auto_generate_proxy_classes'],
            $this->config['orm']['proxy_dir'],
            null
        );

        // Configurar naming strategy
        $ormConfig->setNamingStrategy(new UnderscoreNamingStrategy());

        // EntityManager Master (Primary)
        $masterConnection = DriverManager::getConnection($this->config['dbal']['connections']['default']);
        $this->master = new EntityManager($masterConnection, $ormConfig);

        // EntityManager Slave (Read-only)
        $slaveConnection = DriverManager::getConnection($this->config['dbal']['connections']['slave']);
        $this->slave = new EntityManager($slaveConnection, $ormConfig);
    }
}
