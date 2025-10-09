<?php

declare(strict_types=1);

namespace App\Application\Shared\Http\Middleware\Impl;

use App\Application\Shared\DTOs\Impl\ApiResponse;
use App\Application\Shared\Http\Middleware\ContentTypeValidationMiddlewareInterface;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware para validar Content-Type das requisições
 * Garante que requisições POST/PUT/PATCH tenham Content-Type correto
 */
final class ContentTypeValidationMiddleware implements ContentTypeValidationMiddlewareInterface
{
    private array $allowedContentTypes = [
        'application/json',
        'application/x-www-form-urlencoded',
        'multipart/form-data',
    ];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = $request->getMethod();

        // Apenas validar métodos que podem ter body
        if (! in_array($method, ['POST', 'PUT', 'PATCH'])) {
            return $handler->handle($request);
        }

        $contentType = $request->getHeaderLine('Content-Type');

        // Remover charset se presente para comparação
        $contentType = explode(';', $contentType)[0];
        $contentType = trim($contentType);

        // Verificar se Content-Type é válido
        if (empty($contentType) || ! in_array($contentType, $this->allowedContentTypes)) {
            $response = new Response();

            $apiResponse = new ApiResponse(
                false,
                [
                    'expected_content_types' => $this->allowedContentTypes,
                    'received_content_type' => $contentType ?: 'não fornecido',
                ],
                'Content-Type inválido. Use application/json para requisições JSON.',
                415
            );

            $response->getBody()->write($apiResponse->toJson());

            return $response
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withStatus(415);
        }

        return $handler->handle($request);
    }
}
