<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities\Behaviors;

use DateTime;

interface SoftDeletableBehaviorInterface
{
    public function getDeletedAt(): ?DateTime;

    public function isDeleted(): bool;

    public function restore(): self;

    public function setDeletedAt(?DateTime $deletedAt): self;

    public function softDelete(): self;
}
