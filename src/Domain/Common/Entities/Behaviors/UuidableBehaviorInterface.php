<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities\Behaviors;

interface UuidableBehaviorInterface
{
    public function generateUuid(): self;

    public function getUuid(): ?string;

    public function setUuid(string $uuid): self;
}
