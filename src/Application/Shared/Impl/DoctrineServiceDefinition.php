<?php

declare(strict_types=1);

namespace App\Application\Shared\Impl;

use App\Application\Shared\ServiceDefinitionInterface;
use App\Infrastructure\Common\Database\DoctrineEntityManagerInterface;
use App\Infrastructure\Common\Database\Impl\DoctrineEntityManagerFactory;
use App\Application\Shared\Utils\Impl\ProjectRootDiscovery;
use DI\ContainerBuilder;
use function DI\factory;

final class DoctrineServiceDefinition implements ServiceDefinitionInterface
{
    public static function createEntityManager($container): DoctrineEntityManagerInterface
    {
        $config = $container->get('doctrine.config');
        return DoctrineEntityManagerFactory::create($config);
    }

    public static function loadDoctrineConfig(): array
    {
        // Busca o diretório raiz do projeto de forma dinâmica
        $projectRoot = ProjectRootDiscovery::getProjectRoot();
        $configPath = $projectRoot . '/config/doctrine.php';
        
        if (!file_exists($configPath)) {
            throw new \RuntimeException("Arquivo de configuração do Doctrine não encontrado: {$configPath}");
        }
        
        return require $configPath;
    }

    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            // Configuração do Doctrine
            'doctrine.config' => factory([self::class, 'loadDoctrineConfig']),

            // EntityManager principal
            DoctrineEntityManagerInterface::class => factory([self::class, 'createEntityManager']),
        ]);
    }
}
