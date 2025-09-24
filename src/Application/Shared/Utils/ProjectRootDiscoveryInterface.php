<?php

declare(strict_types=1);

namespace App\Application\Shared\Utils;

/**
 * Interface para descoberta do diretório raiz do projeto
 */
interface ProjectRootDiscoveryInterface
{
    /**
     * Obtém o diretório raiz do projeto
     * 
     * @return string Caminho absoluto para a raiz do projeto
     * @throws \RuntimeException Se não conseguir encontrar a raiz do projeto
     */
    public static function getProjectRoot(): string;

    /**
     * Limpa o cache interno (útil para testes)
     */
    public static function clearCache(): void;
}
