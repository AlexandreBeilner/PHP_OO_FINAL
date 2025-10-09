<?php

declare(strict_types=1);

namespace App\Domain\Products\DTOs\Impl;

/**
 * DTO puro para dados de atualização de produto
 *
 * SRP: Responsabilidade única de transportar dados de atualização
 */
final class UpdateProductDataDTO
{
    public ?string $category;
    public ?string $name;
    public ?float $price;
    public ?string $status;

    public function __construct(
        ?string $name = null,
        ?float $price = null,
        ?string $category = null,
        ?string $status = null
    ) {
        $this->name = $name;
        $this->price = $price;
        $this->category = $category;
        $this->status = $status;
    }

    /**
     * Factory method para criação a partir de array (PHP 7.4 compatível)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'] ?? null,
            isset($data['price']) ? (float) $data['price'] : null,
            $data['category'] ?? null,
            $data['status'] ?? null
        );
    }

    /**
     * Converte para array (apenas campos não-nulos)
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        if ($this->price !== null) {
            $data['price'] = $this->price;
        }
        if ($this->category !== null) {
            $data['category'] = $this->category;
        }
        if ($this->status !== null) {
            $data['status'] = $this->status;
        }

        return $data;
    }
}
