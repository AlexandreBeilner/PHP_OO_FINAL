<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities\Behaviors\Impl;

use App\Domain\Common\Entities\Behaviors\SoftDeletableBehaviorInterface;
use DateTime;

final class SoftDeletableBehavior implements SoftDeletableBehaviorInterface
{
    private ?DateTime $deletedAt = null;

    public function __construct(?DateTime $deletedAt = null)
    {
        $this->deletedAt = $deletedAt;
    }

    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTime $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function getDeletedAtFormatted(string $format = 'Y-m-d H:i:s'): ?string
    {
        return $this->deletedAt ? $this->deletedAt->format($format) : null;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function restore(): self
    {
        $this->deletedAt = null;
        return $this;
    }

    public function softDelete(): self
    {
        $this->deletedAt = new DateTime();
        return $this;
    }
}
