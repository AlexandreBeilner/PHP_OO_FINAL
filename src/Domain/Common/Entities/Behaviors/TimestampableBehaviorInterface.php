<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities\Behaviors;

use DateTime;

interface TimestampableBehaviorInterface
{
    public function touch(): self;
    
    public function getCreatedAtFormatted(string $format = 'Y-m-d H:i:s'): string;
    
    public function getUpdatedAtFormatted(string $format = 'Y-m-d H:i:s'): string;
    
    public function wasCreatedRecently(): bool;
    
    public function wasUpdatedRecently(): bool;
    
    public function neverUpdated(): bool;
    
    public function getAgeInDays(): int;
    
    public function getDaysSinceUpdate(): int;
}
