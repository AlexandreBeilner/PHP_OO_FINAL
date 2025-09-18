<?php

declare(strict_types=1);

namespace App\Application\Common\Http\Middleware\Impl;

use App\Application\Common\Http\Middleware\JsonResponseMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware para padronizar todas as respostas como JSON
 * Garante que todas as respostas tenham Content-Type correto e encoding UTF-8
 */
final class JsonResponseMiddleware implements JsonResponseMiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        // Forçar Content-Type para application/json com charset UTF-8
        $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');

        // Adicionar headers de cache para APIs
        $response = $response->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response = $response->withHeader('Pragma', 'no-cache');
        $response = $response->withHeader('Expires', '0');

        // Adicionar header de segurança
        $response = $response->withHeader('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
