<?php

declare(strict_types=1);

namespace App\Application\Modules\Products\EntityPaths\Impl;

use App\Application\Shared\EntityPaths\EntityPathProviderInterface;
use App\Application\Shared\Utils\Impl\ProjectRootDiscovery;

/**
 * Provedor de entity paths específico do módulo Products
 *
 * SRP: Responsabilidade única de fornecer paths do módulo Products
 * Object Calisthenics: Uma variável de instância, Tell Don't Ask
 */
final class ProductsEntityPathProvider implements EntityPathProviderInterface
{
    private string $productsEntitiesPath;

    /**
     * DI: Injeta dependência via construtor
     * Object Calisthenics: Construtor focado, uma responsabilidade
     */
    public function __construct()
    {
        $this->productsEntitiesPath = $this->buildProductsEntityPath();
    }

    /**
     * Tell Don't Ask: Retorna paths do módulo Products
     * Object Calisthenics: Um nível de indentação
     */
    public function getEntityPaths(): array
    {
        if (!$this->hasEntityPaths()) {
            return [];
        }

        return [$this->productsEntitiesPath];
    }

    /**
     * Tell Don't Ask: Informa se tem paths (sempre true para Products)
     * Object Calisthenics: Não usar else
     */
    public function hasEntityPaths(): bool
    {
        return $this->isProductsEntityPathValid();
    }

    /**
     * SRP: Constrói path das entidades Products
     * Object Calisthenics: Um nível de indentação, método privado focado
     */
    private function buildProductsEntityPath(): string
    {
        return ProjectRootDiscovery::getProjectRoot() . '/src/Domain/Products/Entities/Impl';
    }

    /**
     * SRP: Valida se path existe
     * Object Calisthenics: Método privado com uma responsabilidade
     */
    private function isProductsEntityPathValid(): bool
    {
        return is_dir($this->productsEntitiesPath);
    }
}
