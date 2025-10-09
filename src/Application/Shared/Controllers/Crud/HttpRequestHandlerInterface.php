<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HttpRequestHandlerInterface
{
    /**
     * Manipula a requisição HTTP completa incluindo tratamento de exceções
     */
    public function handle(
        ServerRequestInterface $request,
        ResponseInterface $response,
        CrudOperationInterface $operation,
        array $pathParams = []
    ): ResponseInterface;
}
