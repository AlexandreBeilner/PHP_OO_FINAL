<?php

declare(strict_types=1);

namespace App\Application\Shared\Http\Routing;

use Slim\App;

/**
 * Provedor de rotas básicas da aplicação
 * 
 * Define as rotas essenciais que não pertencem a nenhum módulo específico:
 * - Rota principal (documentação)
 * - Health checks
 * - Status da aplicação
 */
final class CoreRouteProvider implements RouteProviderInterface
{
    public function registerRoutes(App $app): void
    {
        // Rota principal (página inicial)
        $app->get('/', function ($request, $response) {
            $data = [
                'app' => 'Projeto de Treinamento PHP-OO',
                'version' => '1.0.0',
                'endpoints' => [
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
                    ['method' => 'GET', 'path' => '/api/system/extensions-status', 'description' => 'Status das extensões PHP necessárias'],
                    ['method' => 'GET', 'path' => '/api/system/doctrine-test', 'description' => 'Teste completo do Doctrine ORM'],
                ],
            ];

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $data,
                'message' => 'Bem-vindo à API do Projeto de Treinamento PHP-OO',
                'timestamp' => date('Y-m-d H:i:s'),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return $response;
        });

        // Rota de health check principal
        $app->get('/health', function ($request, $response) {
            $data = [
                'status' => 'healthy',
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => '1.0.0',
                'uptime' => 'ok',
            ];

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $data,
                'message' => 'Sistema funcionando corretamente',
                'code' => 200,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return $response;
        });

        // Rota de health check alternativa
        $app->get('/app-status', function ($request, $response) {
            $data = [
                'status' => 'ok',
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => '1.0.0',
            ];

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $data,
                'message' => 'Sistema funcionando corretamente',
                'code' => 200,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return $response;
        });
    }

    public function getRoutePrefix(): string
    {
        return '/'; // Rotas na raiz
    }

    public function getModuleName(): string
    {
        return 'Core';
    }

    public function belongsToModule(string $moduleName): bool
    {
        return $moduleName === 'Core';
    }

    public function getPriority(): int
    {
        return 10; // Alta prioridade - rotas básicas devem ser carregadas primeiro
    }

    public function hasPriorityOver(RouteProviderInterface $other): bool
    {
        // Core tem prioridade 10 (maior) - tem prioridade sobre todos os outros
        return true;
    }
}
