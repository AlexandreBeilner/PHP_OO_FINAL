<?php

declare(strict_types=1);

namespace App\Domain\Products\Services;

use App\Domain\Products\DTOs\Impl\CreateProductDataDTO;
use App\Domain\Products\DTOs\Impl\UpdateProductDataDTO;
use App\Domain\Products\Entities\ProductEntityInterface;

interface ProductServiceInterface
{
    public function activateProduct(int $id): ProductEntityInterface;

    /**
     * Cria produto usando DTO puro (SRP + Tell Don't Ask)
     */
    public function createProduct(CreateProductDataDTO $data): ProductEntityInterface;

    public function deactivateProduct(int $id): ProductEntityInterface;

    public function deleteProduct(int $id): bool;

    public function getAllProducts(): array;

    public function getProductById(int $id): ?ProductEntityInterface;

    public function getProductsByCategory(string $category): array;

    public function saveProduct(ProductEntityInterface $product): ProductEntityInterface;

    public function searchProductsByName(string $name): array;

    /**
     * Atualiza produto usando DTO puro (SRP + Tell Don't Ask)
     */
    public function updateProduct(int $id, UpdateProductDataDTO $data): ProductEntityInterface;
}
