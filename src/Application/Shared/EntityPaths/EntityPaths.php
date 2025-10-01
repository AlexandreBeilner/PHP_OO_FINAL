<?php

declare(strict_types=1);

namespace App\Application\Shared\EntityPaths;

/**
 * Value Object para encapsular caminhos de entidades
 * 
 * Object Calisthenics: Encapsula coleção de strings primitivas
 * SRP: Responsabilidade única de representar uma coleção de paths
 */
final class EntityPaths
{
    private array $paths;

    /**
     * DI: Recebe paths no construtor
     * 
     * @param string[] $paths
     */
    public function __construct(array $paths)
    {
        $this->paths = $this->removeDuplicates($paths);
    }

    /**
     * Object Calisthenics: Tell Don't Ask - verifica se contém paths
     */
    public function hasAnyPath(): bool
    {
        return !empty($this->paths);
    }

    /**
     * Object Calisthenics: Tell Don't Ask - retorna quantidade
     */
    public function count(): int
    {
        return count($this->paths);
    }

    /**
     * Converte para array para compatibilidade com Doctrine
     * 
     * @return string[]
     */
    public function toArray(): array
    {
        return $this->paths;
    }

    /**
     * SRP: Remove duplicatas usando array_unique
     * Object Calisthenics: Um nível de indentação
     * 
     * @param string[] $paths
     * @return string[]
     */
    private function removeDuplicates(array $paths): array
    {
        return array_unique($paths);
    }
}
