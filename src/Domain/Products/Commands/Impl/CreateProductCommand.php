<?php

declare(strict_types=1);

namespace App\Domain\Products\Commands\Impl;

use App\Domain\Products\DTOs\Impl\CreateProductDataDTO;
use App\Domain\Products\Entities\ProductEntityInterface;
use App\Domain\Products\Services\ProductServiceInterface;

/**
 * Command puro para criação de produto
 *
 * SRP: Responsabilidade única de executar ação de criação
 * Usa DTO puro interno para dados
 */
final class CreateProductCommand
{
    private CreateProductDataDTO $data;

    public function __construct(CreateProductDataDTO $data)
    {
        $this->data = $data;
    }

    /**
     * Executa comando de criação (Tell, Don't Ask)
     */
    public function executeWith(ProductServiceInterface $productService): ProductEntityInterface
    {
        return $productService->createProduct($this->data);
    }

    /**
     * Factory method para criação a partir de array
     */
    public static function fromArray(array $data): self
    {
        return new self(CreateProductDataDTO::fromArray($data));
    }
}
