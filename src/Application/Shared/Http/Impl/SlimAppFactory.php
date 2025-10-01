<?php

declare(strict_types=1);

namespace App\Application\Shared\Http\Impl;

use App\Application\ApplicationInterface;
use App\Application\Shared\Http\Handlers\Impl\JsonErrorHandler;
use App\Application\Shared\Http\Middleware\Impl\ContentTypeValidationMiddleware;
use App\Application\Shared\Http\Middleware\Impl\JsonResponseMiddleware;
use App\Application\Shared\Http\Middleware\Impl\NotFoundHandlerMiddleware;
use App\Application\Shared\Http\Middleware\Impl\Utf8EncodingMiddleware;
use App\Application\Shared\Http\SlimAppFactoryInterface;
use App\Application\Shared\Orchestrator\BootstrapOrchestratorInterface;
use App\Application\Shared\Orchestrator\LoaderBundle;
use App\Application\Shared\Orchestrator\Impl\BootstrapOrchestrator;
use App\Application\Shared\Registry\Impl\BootstrapRegistry;
use App\Application\Shared\Loader\Impl\BootstrapLoader;
use App\Application\Shared\Loader\Impl\RouteLoader;
use App\Application\Shared\EntityPaths\Impl\EntityPathCollector;
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

    /**
     * Carrega todas as rotas usando o sistema de roteamento modular
     * Cada módulo define suas próprias rotas através de RouteProviders
     */
    private function loadModularRoutes(App $app): void
    {
        // Cria BootstrapOrchestrator com LoaderBundle (nova assinatura)
        $loaderBundle = new LoaderBundle(
            new BootstrapLoader(),
            new RouteLoader(),
            new EntityPathCollector()
        );
        
        $orchestrator = new BootstrapOrchestrator(
            new BootstrapRegistry(),
            $loaderBundle
        );
        $orchestrator->initializeDefaultBootstraps();
        $orchestrator->loadAllRoutes($app);
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

        // Carrega todas as rotas usando o sistema modular
        $this->loadModularRoutes($app);
    }


}
