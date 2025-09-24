<?php

declare(strict_types=1);

namespace App\Application\Shared\Impl;

use App\Application\Shared\ServiceDefinitionInterface;
use App\Infrastructure\Common\Database\DoctrineEntityManagerInterface;
use App\Infrastructure\Common\Database\Impl\DoctrineEntityManagerFactory;
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
        $projectRoot = self::getProjectRoot();
        $configPath = $projectRoot . '/config/doctrine.php';
        
        if (!file_exists($configPath)) {
            throw new \RuntimeException("Arquivo de configuração do Doctrine não encontrado: {$configPath}");
        }
        
        return require $configPath;
    }
    
    /**
     * Encontra o diretório raiz do projeto de forma dinâmica
     */
    private static function getProjectRoot(): string
    {
        // Procura pelo composer.json para identificar a raiz do projeto
        $currentDir = __DIR__;
        
        while ($currentDir !== '/' && !empty($currentDir)) {
            if (file_exists($currentDir . '/composer.json')) {
                return $currentDir;
            }
            $currentDir = dirname($currentDir);
        }
        
        // Fallback: usa getcwd() se não encontrar composer.json
        $cwd = getcwd();
        if ($cwd && file_exists($cwd . '/composer.json')) {
            return $cwd;
        }
        
        throw new \RuntimeException('Não foi possível encontrar o diretório raiz do projeto');
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
