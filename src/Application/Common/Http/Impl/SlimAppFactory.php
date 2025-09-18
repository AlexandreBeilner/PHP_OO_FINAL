<?php

declare(strict_types=1);

namespace App\Application\Common\Http\Impl;

use App\Application\ApplicationInterface;
use App\Application\Common\Http\Handlers\Impl\JsonErrorHandler;
use App\Application\Common\Http\Middleware\Impl\ContentTypeValidationMiddleware;
use App\Application\Common\Http\Middleware\Impl\JsonResponseMiddleware;
use App\Application\Common\Http\Middleware\Impl\NotFoundHandlerMiddleware;
use App\Application\Common\Http\Middleware\Impl\Utf8EncodingMiddleware;
use App\Application\Common\Http\SlimAppFactoryInterface;
use App\Application\Modules\Auth\Controllers\Impl\AuthController;
use App\Application\Modules\Security\Controllers\Impl\UserController;
use App\Application\Modules\System\Controllers\Impl\SystemController;
use DI\Bridge\Slim\Bridge;
use DI\Container;
use Slim\App;

final class SlimAppFactory implements SlimAppFactoryInterface
{
    private ApplicationInterface $app;
    private ?App $slimApp = null;

    public function __construct(ApplicationInterface $app)
    {
        $this->app = $app;
    }

    public function create(): App
    {
        if ($this->slimApp === null) {
            $this->slimApp = Bridge::create($this->app->container());
            $this->configureApp($this->slimApp);
        }

        return $this->slimApp;
    }

    public function getContainer(): Container
    {
        return $this->app->container();
    }

    private function configureApiRoutes(App $app): void
    {
        // Grupo de rotas da API
        $app->group('/api', function ($group) {
            // Rotas de segurança (usuários)
            $group->group('/security', function ($group) {
                $group->get('/users', [UserController::class, 'index']);
                $group->get('/users/{id:[0-9]+}', [UserController::class, 'show']);
                $group->post('/users', [UserController::class, 'create']);
                $group->put('/users/{id:[0-9]+}', [UserController::class, 'update']);
                $group->delete('/users/{id:[0-9]+}', [UserController::class, 'delete']);
            });

            // Rotas de autenticação
            $group->group('/auth', function ($group) {
                $group->post('/login', [AuthController::class, 'login']);
                $group->post('/change-password', [AuthController::class, 'changePassword']);
                $group->post('/activate/{id:[0-9]+}', [AuthController::class, 'activateUser']);
                $group->post('/deactivate/{id:[0-9]+}', [AuthController::class, 'deactivateUser']);
            });

            // Rotas do sistema
            $group->group('/system', function ($group) {
                $group->get('/info', [SystemController::class, 'getSystemInfo']);
                $group->get('/doctrine-test', [SystemController::class, 'testDoctrine']);
            });
        });
    }

    private function configureApp(App $app): void
    {
        // Configurações básicas do Slim
        $app->setBasePath('');

        // Middleware de parsing JSON (deve ser o primeiro)
        $app->addBodyParsingMiddleware();

        // Middleware de validação de Content-Type
        $app->add(new ContentTypeValidationMiddleware());

        // Middleware de CORS (básico)
        $app->add(function ($request, $handler) {
            $response = $handler->handle($request);
            return $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        });

        // Middleware de padronização de respostas JSON
        $app->add(new JsonResponseMiddleware());

        // Middleware de encoding UTF-8
        $app->add(new Utf8EncodingMiddleware());

        // Middleware para rotas não encontradas
        $app->add(new NotFoundHandlerMiddleware());

        // Middleware de tratamento de erros personalizado
        $errorMiddleware = $app->addErrorMiddleware(true, true, true);
        $errorHandler = new JsonErrorHandler(true, true, true);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        // Configurar rotas básicas
        $this->configureRoutes($app);

        // Configurar rotas da API
        $this->configureApiRoutes($app);
    }

    private function configureRoutes(App $app): void
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

}
