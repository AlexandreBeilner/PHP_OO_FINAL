<?php

declare(strict_types=1);

namespace App\Domain\Products\Repositories;

use App\Domain\Common\Repositories\AbstractRepositoryInterface;
use App\Domain\Products\Entities\ProductEntityInterface;

interface ProductRepositoryInterface extends AbstractRepositoryInterface
{
    public function findActiveProducts(): array;

    public function findByCategory(string $category): array;

    public function findByPriceRange(float $minPrice, float $maxPrice): array;

    public function searchByName(string $name): array;
}
