<?php

declare(strict_types=1);

namespace App\Domain\Products\DTOs\Impl;

/**
 * DTO puro para dados de criação de produto
 *
 * SRP: Responsabilidade única de transportar dados de criação
 */
final class CreateProductDataDTO
{
    public string $category;
    public string $name;
    public float $price;

    public function __construct(
        string $name,
        float $price,
        string $category
    ) {
        $this->name = $name;
        $this->price = $price;
        $this->category = $category;
    }

    /**
     * Factory method para criação a partir de array (PHP 7.4 compatível)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'] ?? '',
            (float) ($data['price'] ?? 0.0),
            $data['category'] ?? ''
        );
    }
}
