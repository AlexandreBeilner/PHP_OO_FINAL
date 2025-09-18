<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities\Behaviors\Impl;

use App\Domain\Common\Entities\Behaviors\TimestampableBehaviorInterface;
use DateTime;

final class TimestampableBehavior implements TimestampableBehaviorInterface
{
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(?DateTime $createdAt = null, ?DateTime $updatedAt = null)
    {
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAtFormatted(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->createdAt->format($format);
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUpdatedAtFormatted(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->updatedAt->format($format);
    }

    public function touch(): self
    {
        $this->updatedAt = new DateTime();
        return $this;
    }
}
