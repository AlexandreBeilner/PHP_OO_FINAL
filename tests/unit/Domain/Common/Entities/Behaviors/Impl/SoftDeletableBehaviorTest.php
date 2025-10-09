<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Common\Entities\Behaviors\Impl;

use App\Domain\Common\Entities\Behaviors\Impl\SoftDeletableBehavior;
use DateTime;
use PHPUnit\Framework\TestCase;

final class SoftDeletableBehaviorTest extends TestCase
{
    public function testCanBeRestoredAtThirtyDayLimit(): void
    {
        $limitDeletedAt = (new DateTime())->modify('-30 days');
        $behavior = new SoftDeletableBehavior($limitDeletedAt);

        $this->assertTrue($behavior->canBeRestored()); // Exactly 30 days should still be restorable
    }

    public function testCanBeRestoredWhenNotDeleted(): void
    {
        $behavior = new SoftDeletableBehavior();

        $this->assertFalse($behavior->canBeRestored());
    }

    public function testCanBeRestoredWithOldDeletion(): void
    {
        $oldDeletedAt = (new DateTime())->modify('-31 days');
        $behavior = new SoftDeletableBehavior($oldDeletedAt);

        $this->assertFalse($behavior->canBeRestored()); // More than 30 days
    }

    public function testCanBeRestoredWithRecentDeletion(): void
    {
        $recentDeletedAt = (new DateTime())->modify('-5 days');
        $behavior = new SoftDeletableBehavior($recentDeletedAt);

        $this->assertTrue($behavior->canBeRestored()); // Within 30 days
    }

    public function testConstructorWithDeletedAt(): void
    {
        $deletedAt = new DateTime('2023-01-15 10:30:00');
        $behavior = new SoftDeletableBehavior($deletedAt);

        $this->assertTrue($behavior->isDeleted());
        $this->assertEquals('2023-01-15 10:30:00', $behavior->getDeletedAtFormatted());
    }

    public function testConstructorWithoutDeletedAt(): void
    {
        $behavior = new SoftDeletableBehavior();

        $this->assertFalse($behavior->isDeleted());
        $this->assertNull($behavior->getDeletedAtFormatted());
        $this->assertFalse($behavior->wasDeletedRecently());
        $this->assertEquals(0, $behavior->getDaysSinceDeletion());
        $this->assertFalse($behavior->canBeRestored());
    }

    public function testGetDaysSinceDeletionWhenNotDeleted(): void
    {
        $behavior = new SoftDeletableBehavior();

        $this->assertEquals(0, $behavior->getDaysSinceDeletion());
    }

    public function testGetDaysSinceDeletionWithOldDeletion(): void
    {
        $deletedAt = (new DateTime())->modify('-5 days');
        $behavior = new SoftDeletableBehavior($deletedAt);

        $daysSince = $behavior->getDaysSinceDeletion();

        $this->assertGreaterThanOrEqual(4, $daysSince); // At least 4 days
        $this->assertLessThanOrEqual(5, $daysSince);    // At most 5 days
    }

    public function testGetDeletedAtFormattedWhenNotDeleted(): void
    {
        $behavior = new SoftDeletableBehavior();

        $this->assertNull($behavior->getDeletedAtFormatted());
        $this->assertNull($behavior->getDeletedAtFormatted('d/m/Y H:i'));
    }

    public function testGetDeletedAtFormattedWithCustomFormat(): void
    {
        $deletedAt = new DateTime('2023-03-15 14:25:30');
        $behavior = new SoftDeletableBehavior($deletedAt);

        $this->assertEquals('15/03/2023', $behavior->getDeletedAtFormatted('d/m/Y'));
        $this->assertEquals('2023-03-15 14:25', $behavior->getDeletedAtFormatted('Y-m-d H:i'));
    }

    public function testRestoreRemovesDeletedAt(): void
    {
        $deletedAt = new DateTime('2023-01-15 10:30:00');
        $behavior = new SoftDeletableBehavior($deletedAt);

        $this->assertTrue($behavior->isDeleted());

        $result = $behavior->restore();

        $this->assertSame($behavior, $result); // Returns self
        $this->assertFalse($behavior->isDeleted());
        $this->assertNull($behavior->getDeletedAtFormatted());
    }

    public function testSoftDeleteAndRestoreCycle(): void
    {
        $behavior = new SoftDeletableBehavior();

        // Initially not deleted
        $this->assertFalse($behavior->isDeleted());

        // Soft delete
        $behavior->softDelete();
        $this->assertTrue($behavior->isDeleted());
        $this->assertTrue($behavior->wasDeletedRecently());

        // Restore
        $behavior->restore();
        $this->assertFalse($behavior->isDeleted());
        $this->assertFalse($behavior->wasDeletedRecently());
        $this->assertEquals(0, $behavior->getDaysSinceDeletion());

        // Soft delete again
        $behavior->softDelete();
        $this->assertTrue($behavior->isDeleted());
    }

    public function testSoftDeleteDoesNotOverwriteExistingDeletedAt(): void
    {
        $originalDeletedAt = new DateTime('2023-01-01 12:00:00');
        $behavior = new SoftDeletableBehavior($originalDeletedAt);

        $behavior->softDelete();

        $this->assertEquals('2023-01-01 12:00:00', $behavior->getDeletedAtFormatted());
    }

    public function testSoftDeleteSetsDeletedAtTimestamp(): void
    {
        $behavior = new SoftDeletableBehavior();

        $this->assertFalse($behavior->isDeleted());

        $result = $behavior->softDelete();

        $this->assertSame($behavior, $result); // Returns self
        $this->assertTrue($behavior->isDeleted());
        $this->assertNotNull($behavior->getDeletedAtFormatted());
    }

    public function testWasDeletedRecentlyWhenNotDeleted(): void
    {
        $behavior = new SoftDeletableBehavior();

        $this->assertFalse($behavior->wasDeletedRecently());
    }

    public function testWasDeletedRecentlyWithOldDeletion(): void
    {
        $oldDeletedAt = (new DateTime())->modify('-2 days');
        $behavior = new SoftDeletableBehavior($oldDeletedAt);

        $this->assertFalse($behavior->wasDeletedRecently());
    }

    public function testWasDeletedRecentlyWithRecentDeletion(): void
    {
        $recentDeletedAt = (new DateTime())->modify('-12 hours');
        $behavior = new SoftDeletableBehavior($recentDeletedAt);

        $this->assertTrue($behavior->wasDeletedRecently());
    }
}
