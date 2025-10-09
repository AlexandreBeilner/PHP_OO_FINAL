<?php

declare(strict_types=1);

namespace App\Application\Shared\Utils\Impl;

use App\Application\Shared\Utils\ProjectRootDiscoveryInterface;
use RuntimeException;

/**
 * Utilitário para descobrir e cachear o diretório raiz do projeto
 *
 * Evita duplicação de código e melhora performance com cache
 * Procura pelo composer.json para identificar a raiz do projeto
 */
final class ProjectRootDiscovery implements ProjectRootDiscoveryInterface
{
    private static ?string $cachedRoot = null;

    /**
     * Limpa o cache (útil para testes)
     */
    public static function clearCache(): void
    {
        self::$cachedRoot = null;
    }

    /**
     * Obtém o diretório raiz do projeto com cache
     *
     * @return string Caminho absoluto para a raiz do projeto
     * @throws RuntimeException Se não conseguir encontrar a raiz do projeto
     */
    public static function getProjectRoot(): string
    {
        if (self::$cachedRoot !== null) {
            return self::$cachedRoot;
        }

        self::$cachedRoot = self::discoverProjectRoot();
        return self::$cachedRoot;
    }

    /**
     * Descobre o diretório raiz do projeto procurando pelo composer.json
     *
     * @return string Caminho absoluto para a raiz do projeto
     * @throws RuntimeException Se não conseguir encontrar a raiz do projeto
     */
    private static function discoverProjectRoot(): string
    {
        // Começar pelo diretório atual da classe
        $currentDir = __DIR__;

        // Procurar composer.json subindo na hierarquia de diretórios
        while ($currentDir !== '/' && ! empty($currentDir)) {
            if (file_exists($currentDir . '/composer.json')) {
                return $currentDir;
            }
            $currentDir = dirname($currentDir);
        }

        // Fallback: tentar getcwd() se não encontrou pelo __DIR__
        $cwd = getcwd();
        if ($cwd && file_exists($cwd . '/composer.json')) {
            return $cwd;
        }

        throw new RuntimeException('Não foi possível encontrar o diretório raiz do projeto');
    }
}
