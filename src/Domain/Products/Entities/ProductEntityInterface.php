<?php

declare(strict_types=1);

namespace App\Domain\Products\Entities;

use App\Domain\Common\Entities\Behaviors\TimestampableBehaviorInterface;
use App\Domain\Common\Entities\Behaviors\UuidableBehaviorInterface;

interface ProductEntityInterface extends TimestampableBehaviorInterface, UuidableBehaviorInterface
{
    public function activate(): self;

    public function belongsToCategory(string $category): bool;

    public function canBeSold(): bool;

    public function deactivate(): self;

    public function getId(): int;

    public function getName(): string;

    public function getPrice(): float;

    public function getCategory(): string;

    public function getStatus(): string;

    public function isActive(): bool;

    public function isPriceValid(): bool;

    public function updatePrice(float $newPrice): self;
}
