<?php

declare(strict_types=1);

namespace App\Application\Modules\Common\Bootstrap\Impl;

use App\Application\Modules\Common\Bootstrap\ServiceDefinitionInterface;
use App\Common\Database\DoctrineEntityManagerInterface;
use App\Common\Database\Impl\DoctrineEntityManagerFactory;
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
        return require '/opt/project/config/doctrine.php';
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
