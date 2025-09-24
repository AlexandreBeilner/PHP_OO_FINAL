<?php

declare(strict_types=1);

namespace App\Application\Shared\Http\Middleware\Impl;

use App\Application\Shared\DTOs\Impl\ApiResponse;
use App\Application\Shared\Http\Middleware\NotFoundHandlerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware para padronizar respostas de rotas não encontradas
 * Garante que 404 sempre retorne JSON padronizado
 */
final class NotFoundHandlerMiddleware implements NotFoundHandlerMiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        // Se a resposta é 404, padronizar como JSON
        if ($response->getStatusCode() === 404) {
            $apiResponse = new ApiResponse(
                false,
                [
                    'path' => $request->getUri()->getPath(),
                    'method' => $request->getMethod(),
                    'available_endpoints' => $this->getAvailableEndpoints(),
                ],
                'Rota não encontrada',
                404
            );

            $response->getBody()->write($apiResponse->toJson());
            $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        }

        return $response;
    }

    private function getAvailableEndpoints(): array
    {
        return [
            // Rotas básicas
            ['method' => 'GET', 'path' => '/', 'description' => 'Página inicial com documentação da API'],
            ['method' => 'GET', 'path' => '/health', 'description' => 'Verificação de saúde do sistema'],
            ['method' => 'GET', 'path' => '/app-status', 'description' => 'Status detalhado da aplicação'],
            
            // Rotas de segurança (usuários)
            ['method' => 'GET', 'path' => '/api/security/users', 'description' => 'Listar usuários'],
            ['method' => 'POST', 'path' => '/api/security/users', 'description' => 'Criar usuário'],
            ['method' => 'GET', 'path' => '/api/security/users/{id}', 'description' => 'Buscar usuário por ID'],
            ['method' => 'PUT', 'path' => '/api/security/users/{id}', 'description' => 'Atualizar usuário'],
            ['method' => 'DELETE', 'path' => '/api/security/users/{id}', 'description' => 'Deletar usuário'],
            
            // Rotas de autenticação
            ['method' => 'POST', 'path' => '/api/auth/login', 'description' => 'Login do usuário'],
            ['method' => 'POST', 'path' => '/api/auth/change-password', 'description' => 'Alterar senha do usuário'],
            ['method' => 'POST', 'path' => '/api/auth/activate/{id}', 'description' => 'Ativar usuário'],
            ['method' => 'POST', 'path' => '/api/auth/deactivate/{id}', 'description' => 'Desativar usuário'],
            
            // Rotas do sistema
            ['method' => 'GET', 'path' => '/api/system/info', 'description' => 'Informações detalhadas do sistema'],
            ['method' => 'GET', 'path' => '/api/system/doctrine-test', 'description' => 'Teste completo do Doctrine ORM'],
        ];
    }
}
