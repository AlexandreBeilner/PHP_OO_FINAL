<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud;

use App\Application\Shared\DTOs\ApiResponseInterface;
use Exception;

interface ExceptionHandlerInterface
{
    /**
     * Converte exceções em respostas API padronizadas
     */
    public function handle(Exception $exception): ApiResponseInterface;
    
    /**
     * Registra um handler customizado para um tipo específico de exceção
     */
    public function registerHandler(string $exceptionClass, callable $handler): void;
}
