<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities\Behaviors;

use DateTime;

interface SoftDeletableBehaviorInterface
{
    public function getDeletedAtFormatted(string $format = 'Y-m-d H:i:s'): ?string;
    
    public function isDeleted(): bool;
    
    public function restore(): self;
    
    public function softDelete(): self;
    
    public function wasDeletedRecently(): bool;
    
    public function getDaysSinceDeletion(): int;
    
    public function canBeRestored(): bool;
}
