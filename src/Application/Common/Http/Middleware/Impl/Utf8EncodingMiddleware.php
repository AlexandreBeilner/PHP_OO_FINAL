<?php

declare(strict_types=1);

namespace App\Application\Common\Http\Middleware\Impl;

use App\Application\Common\Http\Middleware\Utf8EncodingMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware para garantir encoding UTF-8 em todas as respostas
 * Converte automaticamente strings para UTF-8 se necessário
 */
final class Utf8EncodingMiddleware implements Utf8EncodingMiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        // Verificar se o body da resposta precisa de conversão UTF-8
        $body = $response->getBody();
        $body->rewind();
        $content = $body->getContents();

        // Verificar se o conteúdo já está em UTF-8
        if (! mb_check_encoding($content, 'UTF-8')) {
            // Converter para UTF-8
            $content = mb_convert_encoding($content, 'UTF-8', 'auto');
        }

        // Garantir que o JSON está bem formado
        if ($this->isJsonResponse($response)) {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // Re-encode com flags para garantir UTF-8
                $content = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        // Reescrever o body com conteúdo UTF-8
        $body->rewind();
        $body->write($content);

        return $response;
    }

    private function isJsonResponse(ResponseInterface $response): bool
    {
        $contentType = $response->getHeaderLine('Content-Type');
        return strpos($contentType, 'application/json') !== false;
    }
}
