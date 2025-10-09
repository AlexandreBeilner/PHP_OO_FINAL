<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities\Behaviors;

interface UuidableBehaviorInterface
{
    public function generateUuid(): self;

    public function getUuid(): ?string;

    public function hasUuid(): bool;

    public function hasValidUuid(): bool;

    public function matchesUuid(string $otherUuid): bool;

    public function regenerateUuid(): self;
}
