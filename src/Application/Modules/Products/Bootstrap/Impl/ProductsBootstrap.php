<?php

declare(strict_types=1);

namespace App\Application\Modules\Products\Bootstrap\Impl;

use App\Application\Modules\Products\EntityPaths\Impl\ProductsEntityPathProvider;
use App\Application\Modules\Products\Http\Routing\Impl\ProductsRouteProvider;
use App\Application\Modules\System\Bootstrap\Impl\SystemBootstrap;
use App\Application\Shared\BootstrapInterface;
use App\Application\Shared\EntityPaths\EntityPathProviderInterface;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use App\Application\Shared\Impl\AbstractBootstrap;
use App\Application\Shared\Impl\CommonBootstrap;
use DI\ContainerBuilder;

final class ProductsBootstrap extends AbstractBootstrap implements BootstrapInterface
{
    public function belongsToModule(string $moduleName): bool
    {
        return $moduleName === 'Products';
    }

    /**
     * Factory Method: Cria ProductsEntityPathProvider
     * Object Calisthenics: Lazy initialization para não exceder 2 variáveis de instância
     * DI: Cria dependência via factory method
     */
    public function getEntityPathProvider(): ?EntityPathProviderInterface
    {
        return $this->createProductsEntityPathProvider();
    }

    public function getModuleName(): string
    {
        return 'Products';
    }

    public function getPriority(): int
    {
        return 50; // Prioridade após System (30) e Auth (25)
    }

    public function getRouteProvider(): ?RouteProviderInterface
    {
        return new ProductsRouteProvider();
    }

    /**
     * Tell Don't Ask: Informa que Products possui entity path provider
     * Object Calisthenics: Método simples, uma responsabilidade
     */
    public function hasEntityPathProvider(): bool
    {
        return true;
    }

    public function hasPriorityOver(BootstrapInterface $other): bool
    {
        // Products tem prioridade 40
        if ($other instanceof CommonBootstrap) {
            return false; // Common (10) tem prioridade maior
        }
        if ($other instanceof SystemBootstrap) {
            return false; // System (30) tem prioridade maior
        }
        return $this->getPriority() < $other->getPriority();
    }

    public function hasRoutes(): bool
    {
        return true;
    }

    /**
     * Registro do módulo de produtos
     *
     * Todos os serviços implementam padrão Tell, Don't Ask
     * Commands usam Factory Pattern e não são registrados no container DI
     * ValidationServices criam Commands sob demanda via métodos factory fromArray()
     */
    public function register(ContainerBuilder $builder): void
    {
        $this->loadServiceDefinitions($builder, [
            ProductServiceDefinition::class,
            ProductValidationServiceDefinition::class,
            ProductControllerDefinition::class,
        ]);
    }

    /**
     * Factory Method: Cria ProductsEntityPathProvider
     * SRP: Responsabilidade única de criar entity path provider
     * Object Calisthenics: Método privado focado
     */
    private function createProductsEntityPathProvider(): EntityPathProviderInterface
    {
        return new ProductsEntityPathProvider();
    }
}
