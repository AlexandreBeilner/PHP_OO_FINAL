<?php

declare(strict_types=1);

namespace App\Domain\System\Services;

interface SystemServiceInterface
{
    /**
     * Limpa o cache da aplicação
     */
    public function clearCache(): array;

    /**
     * Verifica status das extensões PHP necessárias
     */
    public function getRequiredExtensionsStatus(): array;

    /**
     * Obtém informações do sistema
     */
    public function getSystemInfo(): array;

    /**
     * Remove diretório de cache
     */
    public function removeDirectory(string $path): bool;

    /**
     * Testa a conexão com o banco de dados
     */
    public function testDatabase(): array;
}
