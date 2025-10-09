<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities\Behaviors;

interface TimestampableBehaviorInterface
{
    public function getAgeInDays(): int;

    public function getCreatedAtFormatted(string $format = 'Y-m-d H:i:s'): string;

    public function getDaysSinceUpdate(): int;

    public function getUpdatedAtFormatted(string $format = 'Y-m-d H:i:s'): string;

    public function neverUpdated(): bool;

    public function touch(): self;

    public function wasCreatedRecently(): bool;

    public function wasUpdatedRecently(): bool;
}
