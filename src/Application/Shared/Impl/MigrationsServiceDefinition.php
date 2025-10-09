<?php

declare(strict_types=1);

namespace App\Application\Shared\Impl;

use App\Application\Shared\ServiceDefinitionInterface;
use App\Application\Shared\Utils\Impl\ProjectRootDiscovery;
use App\Infrastructure\Common\Database\DoctrineEntityManagerInterface;
use DI\ContainerBuilder;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use function DI\factory;

/**
 * Service Definition para Doctrine Migrations usando o EntityManager existente do projeto
 */
final class MigrationsServiceDefinition implements ServiceDefinitionInterface
{
    public static function createMigrationsConfiguration(): array
    {
        return [
            'table_storage' => [
                'table_name' => 'doctrine_migration_versions',
                'version_column_name' => 'version',
                'version_column_length' => 191,
                'executed_at_column_name' => 'executed_at',
            ],
            'migrations_paths' => [
                'App\\Infrastructure\\Common\\Database\\Migrations' => ProjectRootDiscovery::getProjectRoot() . '/src/Infrastructure/Common/Database/Migrations',
            ],
            'all_or_nothing' => true,
            'check_database_platform' => true,
            'organize_migrations' => 'year',
        ];
    }

    public static function createMigrationsDependencyFactory($container): DependencyFactory
    {
        // Usar EntityManager existente do projeto (master para writes/migrations)
        $doctrineManager = $container->get(DoctrineEntityManagerInterface::class);
        $entityManager = $doctrineManager->getMaster();

        // Configuração das migrations
        $migrationsConfig = $container->get('migrations.config');
        $configuration = new ConfigurationArray($migrationsConfig);

        // Usar conexão existente do EntityManager
        $connectionLoader = new ExistingConnection($entityManager->getConnection());

        return DependencyFactory::fromConnection($configuration, $connectionLoader);
    }

    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            // Configuração das migrations
            'migrations.config' => factory([self::class, 'createMigrationsConfiguration']),

            // DependencyFactory do Doctrine Migrations usando EntityManager existente
            DependencyFactory::class => factory([self::class, 'createMigrationsDependencyFactory']),
        ]);
    }
}
