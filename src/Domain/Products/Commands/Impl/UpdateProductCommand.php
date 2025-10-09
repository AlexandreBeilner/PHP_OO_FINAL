<?php

declare(strict_types=1);

namespace App\Domain\Products\Commands\Impl;

use App\Domain\Products\DTOs\Impl\UpdateProductDataDTO;
use App\Domain\Products\Entities\ProductEntityInterface;
use App\Domain\Products\Services\ProductServiceInterface;

/**
 * Command puro para atualização de produto
 *
 * SRP: Responsabilidade única de executar ação de atualização
 * Usa DTO puro interno para dados
 */
final class UpdateProductCommand
{
    private UpdateProductDataDTO $data;

    public function __construct(UpdateProductDataDTO $data)
    {
        $this->data = $data;
    }

    /**
     * Executa comando de atualização (Tell, Don't Ask)
     */
    public function executeWithProductId(ProductServiceInterface $productService, int $productId): ?ProductEntityInterface
    {
        return $productService->updateProduct($productId, $this->data);
    }

    /**
     * Factory method para criação a partir de array
     */
    public static function fromArray(array $data): self
    {
        return new self(UpdateProductDataDTO::fromArray($data));
    }
}
