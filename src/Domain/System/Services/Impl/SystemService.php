<?php

declare(strict_types=1);

namespace App\Domain\System\Services\Impl;

use App\Infrastructure\Common\Database\DoctrineEntityManagerInterface;
use App\Domain\System\Services\SystemServiceInterface;
use Doctrine\ORM\Version;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Slim\App;

final class SystemService implements SystemServiceInterface
{
    private DoctrineEntityManagerInterface $doctrineManager;

    public function __construct(DoctrineEntityManagerInterface $doctrineManager)
    {
        $this->doctrineManager = $doctrineManager;
    }

    public function clearCache(): array
    {
        try {
            $cacheDir = __DIR__ . '/../../../../cache';
            $removed = $this->removeDirectory($cacheDir);

            if ($removed) {
                return [
                    'status' => 'success',
                    'message' => 'Cache limpo com sucesso',
                    'cache_directory' => $cacheDir,
                    'removed' => true,
                ];
            } else {
                return [
                    'status' => 'warning',
                    'message' => 'Cache não pôde ser completamente limpo',
                    'cache_directory' => $cacheDir,
                    'removed' => false,
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao limpar cache: ' . $e->getMessage(),
                'error_code' => $e->getCode(),
            ];
        }
    }

    public function getRequiredExtensionsStatus(): array
    {
        $requiredExtensions = [
            'pdo' => 'PDO - PHP Data Objects',
            'pdo_pgsql' => 'PDO PostgreSQL Driver',
            'json' => 'JSON',
            'mbstring' => 'Multibyte String',
            'openssl' => 'OpenSSL',
            'curl' => 'cURL',
            'zip' => 'ZIP',
            'gd' => 'GD Library',
            'intl' => 'Internationalization',
            'xml' => 'XML',
            'dom' => 'DOM',
            'simplexml' => 'SimpleXML',
            'fileinfo' => 'File Information',
        ];

        $status = [];
        $allLoaded = true;

        foreach ($requiredExtensions as $extension => $description) {
            $loaded = extension_loaded($extension);
            $status[$extension] = [
                'loaded' => $loaded,
                'description' => $description,
            ];

            if (! $loaded) {
                $allLoaded = false;
            }
        }

        return [
            'all_loaded' => $allLoaded,
            'extensions' => $status,
            'total_required' => count($requiredExtensions),
            'loaded_count' => count(array_filter($status, fn ($ext) => $ext['loaded'])),
        ];
    }

    public function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'php_sapi' => PHP_SAPI,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'timezone' => date_default_timezone_get(),
            'server_time' => date('Y-m-d H:i:s'),
            'unix_timestamp' => time(),
            'doctrine_version' => Version::VERSION,
            'slim_version' => App::VERSION ?? 'N/A',
            'environment' => $_ENV['APP_ENV'] ?? 'development',
            'debug_mode' => $_ENV['APP_DEBUG'] ?? false,
        ];
    }

    public function removeDirectory(string $path): bool
    {
        if (! is_dir($path)) {
            return true;
        }

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }

            return rmdir($path);
        } catch (Exception $e) {
            return false;
        }
    }

    public function testDatabase(): array
    {
        try {
            $entityManager = $this->doctrineManager->getMaster();

            // Testa conexão básica
            $connection = $entityManager->getConnection();
            $connection->connect();

            // Testa query simples
            $result = $connection->executeQuery('SELECT 1 as test')->fetchAssociative();

            // Testa metadados de entidades
            $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
            $entities = [];

            foreach ($metadata as $meta) {
                $entities[] = [
                    'name' => $meta->getName(),
                    'table' => $meta->getTableName(),
                    'fields' => array_values($meta->getFieldNames()),
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Conexão com banco de dados estabelecida com sucesso',
                'connection_test' => $result,
                'entities_count' => count($entities),
                'entities' => $entities,
                'doctrine_version' => Version::VERSION,
            ];
        } catch (Exception $e) {
            // Re-throw the exception so the controller can handle it properly
            throw new Exception('Erro ao conectar com banco de dados: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
