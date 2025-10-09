<?php

declare(strict_types=1);

namespace App\Application\Shared\Http\Routing;

use Slim\App;

/**
 * Gerenciador de provedores de rotas modulares
 *
 * Centraliza o registro de todos os RouteProviders dos módulos,
 * seguindo o mesmo padrão do BootstrapManager para consistência arquitetural
 */
final class RouteProviderManager
{
    /**
     * @var RouteProviderInterface[]
     */
    private array $routeProviders = [];

    /**
     * Busca um provedor de rotas por nome do módulo
     *
     * @param string $moduleName
     * @return RouteProviderInterface|null
     */
    public function getRouteProviderByModule(string $moduleName): ?RouteProviderInterface
    {
        foreach ($this->routeProviders as $routeProvider) {
            if ($routeProvider->belongsToModule($moduleName)) {
                return $routeProvider;
            }
        }

        return null;
    }

    /**
     * Retorna todos os provedores de rotas registrados
     *
     * @return RouteProviderInterface[]
     */
    public function getRouteProviders(): array
    {
        return $this->routeProviders;
    }

    /**
     * Carrega todas as rotas registradas na aplicação Slim
     * Ordena por prioridade antes de registrar
     *
     * @param App $app Instância da aplicação Slim
     */
    public function loadAllRoutes(App $app): void
    {
        // Ordena por prioridade usando Tell Don't Ask
        usort($this->routeProviders, function (RouteProviderInterface $a, RouteProviderInterface $b) {
            if ($a->hasPriorityOver($b)) {
                return -1; // a vem antes de b
            }
            if ($b->hasPriorityOver($a)) {
                return 1;  // b vem antes de a
            }
            return 0; // mesma prioridade
        });

        foreach ($this->routeProviders as $routeProvider) {
            $routeProvider->registerRoutes($app);
        }
    }

    /**
     * Registra um novo provedor de rotas
     *
     * @param RouteProviderInterface $routeProvider
     */
    public function registerRouteProvider(RouteProviderInterface $routeProvider): void
    {
        $this->routeProviders[] = $routeProvider;
    }

    /**
     * Registra múltiplos provedores de rotas de uma vez
     *
     * @param RouteProviderInterface[] $routeProviders
     */
    public function registerRouteProviders(array $routeProviders): void
    {
        foreach ($routeProviders as $routeProvider) {
            $this->registerRouteProvider($routeProvider);
        }
    }

}
