<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities\Behaviors;

use DateTime;

interface TimestampableBehaviorInterface
{
    public function getCreatedAt(): DateTime;

    public function getCreatedAtFormatted(string $format = 'Y-m-d H:i:s'): string;

    public function getUpdatedAt(): DateTime;

    public function getUpdatedAtFormatted(string $format = 'Y-m-d H:i:s'): string;

    public function setCreatedAt(DateTime $createdAt): self;

    public function setUpdatedAt(DateTime $updatedAt): self;

    public function touch(): self;
}
