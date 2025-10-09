<?php

declare(strict_types=1);

namespace App\Domain\Products\Services\Impl;

use App\Domain\Common\Exceptions\Impl\ValidationException;
use App\Domain\Products\DTOs\Impl\CreateProductDataDTO;
use App\Domain\Products\DTOs\Impl\UpdateProductDataDTO;
use App\Domain\Products\Entities\Impl\ProductEntity;
use App\Domain\Products\Entities\ProductEntityInterface;
use App\Domain\Products\Repositories\ProductRepositoryInterface;
use App\Domain\Products\Services\ProductServiceInterface;
use App\Domain\Products\Validators\ProductDataValidatorInterface;
use InvalidArgumentException;

final class ProductService implements ProductServiceInterface
{
    private ProductRepositoryInterface $repository;
    private ProductDataValidatorInterface $productValidator;

    public function __construct(
        ProductRepositoryInterface $repository,
        ProductDataValidatorInterface $productValidator
    ) {
        $this->repository = $repository;
        $this->productValidator = $productValidator;
    }

    public function count(array $criteria = []): int
    {
        return $this->repository->count($criteria);
    }

    public function delete(object $entity): bool
    {
        return $this->repository->delete($entity);
    }

    public function exists(int $id): bool
    {
        return $this->repository->count(['id' => $id]) > 0;
    }

    public function save(object $entity): object
    {
        return $this->repository->save($entity);
    }

    public function activateProduct(int $id): ProductEntityInterface
    {
        return $this->processProductById($id, function ($product) {
            $product->activate();
            return $this->repository->save($product);
        });
    }

    /**
     * Cria produto usando DTO puro (SRP + Tell Don't Ask)
     */
    public function createProduct(CreateProductDataDTO $data): ProductEntityInterface
    {
        // Validações de domínio usando dados do DTO
        $this->validateProductName($data->name);
        $this->validateProductPrice($data->price);
        $this->validateProductCategory($data->category);

        // Criação da entidade usando propriedades readonly do DTO
        $product = new ProductEntity(
            $data->name,
            $data->price,
            $data->category,
            'draft'
        );

        return $this->repository->save($product);
    }

    public function deactivateProduct(int $id): ProductEntityInterface
    {
        return $this->processProductById($id, function ($product) {
            $product->deactivate();
            return $this->repository->save($product);
        });
    }

    public function deleteProduct(int $id): bool
    {
        $product = $this->repository->find($id);
        if (!$product) {
            return false;
        }

        return $this->repository->delete($product);
    }

    public function getAllProducts(): array
    {
        return $this->repository->findAll();
    }

    public function getProductById(int $id): ?ProductEntityInterface
    {
        return $this->repository->find($id);
    }

    public function getProductsByCategory(string $category): array
    {
        return $this->repository->findByCategory($category);
    }

    public function saveProduct(ProductEntityInterface $product): ProductEntityInterface
    {
        return $this->repository->save($product);
    }

    public function searchProductsByName(string $name): array
    {
        return $this->repository->searchByName($name);
    }

    public function processAllProducts(callable $action): array
    {
        $products = $this->repository->findAll();
        $results = [];

        foreach ($products as $product) {
            $results[] = $action($product);
        }

        return $results;
    }

    public function processProductById(int $id, callable $action)
    {
        $product = $this->repository->find($id);
        if (!$product) {
            throw new InvalidArgumentException("Product with ID $id not found");
        }

        return $action($product);
    }

    /**
     * Atualiza produto usando DTO puro (SRP + Tell Don't Ask)
     */
    public function updateProduct(int $id, UpdateProductDataDTO $data): ProductEntityInterface
    {
        $product = $this->repository->find($id);
        if (!$product) {
            throw new InvalidArgumentException("Product with ID $id not found");
        }

        // Aplicar mudanças usando Tell Don't Ask
        if ($data->name !== null) {
            $this->validateProductName($data->name);
            $product->name = $data->name;
        }

        if ($data->price !== null) {
            $this->validateProductPrice($data->price);
            $product->updatePrice($data->price);
        }

        if ($data->category !== null) {
            $this->validateProductCategory($data->category);
            $product->category = $data->category;
        }

        if ($data->status !== null) {
            $this->validateProductStatus($data->status);
            if ($data->status === 'active') {
                $product->activate();
            } elseif ($data->status === 'inactive') {
                $product->deactivate();
            } else {
                $product->status = $data->status;
            }
        }

        $product->touchEntity();

        return $this->repository->save($product);
    }

    protected function extractEntityData(object $entity): array
    {
        if (!$entity instanceof ProductEntity) {
            throw new InvalidArgumentException('Entity must be instance of ProductEntity');
        }

        return [
            'name' => $entity->name,
            'price' => $entity->price,
            'category' => $entity->category,
            'status' => $entity->status
        ];
    }


    private function validateProductCategory(string $category): void
    {
        if (empty(trim($category))) {
            throw new ValidationException('Product category cannot be empty');
        }

        if (strlen(trim($category)) < 2) {
            throw new ValidationException('Product category must have at least 2 characters');
        }
    }

    private function validateProductName(string $name): void
    {
        if (empty(trim($name))) {
            throw new ValidationException('Product name cannot be empty');
        }

        if (strlen(trim($name)) < 2) {
            throw new ValidationException('Product name must have at least 2 characters');
        }
    }

    private function validateProductPrice(float $price): void
    {
        if ($price <= 0) {
            throw new ValidationException('Product price must be greater than zero');
        }
    }

    private function validateProductStatus(string $status): void
    {
        $validStatuses = ['draft', 'active', 'inactive'];
        if (!in_array($status, $validStatuses, true)) {
            throw new ValidationException('Invalid product status. Must be one of: ' . implode(', ', $validStatuses));
        }
    }
}
