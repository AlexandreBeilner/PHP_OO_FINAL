<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Common\Entities\Behaviors\Impl;

use App\Domain\Common\Entities\Behaviors\Impl\TimestampableBehavior;
use DateTime;
use PHPUnit\Framework\TestCase;

final class TimestampableBehaviorTest extends TestCase
{
    public function testConstructorWithNoParameters(): void
    {
        $behavior = new TimestampableBehavior();

        $this->assertInstanceOf(DateTime::class, new DateTime($behavior->getCreatedAtFormatted()));
        $this->assertInstanceOf(DateTime::class, new DateTime($behavior->getUpdatedAtFormatted()));
    }

    public function testConstructorWithSpecificDates(): void
    {
        $createdAt = new DateTime('2023-01-01 10:00:00');
        $updatedAt = new DateTime('2023-01-02 15:30:00');

        $behavior = new TimestampableBehavior($createdAt, $updatedAt);

        $this->assertEquals('2023-01-01 10:00:00', $behavior->getCreatedAtFormatted());
        $this->assertEquals('2023-01-02 15:30:00', $behavior->getUpdatedAtFormatted());
    }

    public function testGetCreatedAtFormattedWithCustomFormat(): void
    {
        $createdAt = new DateTime('2023-03-15 14:25:30');
        $behavior = new TimestampableBehavior($createdAt, new DateTime());

        $this->assertEquals('15/03/2023', $behavior->getCreatedAtFormatted('d/m/Y'));
        $this->assertEquals('2023-03-15', $behavior->getCreatedAtFormatted('Y-m-d'));
    }

    public function testGetCreatedAtFormattedWithDefaultFormat(): void
    {
        $createdAt = new DateTime('2023-03-15 14:25:30');
        $behavior = new TimestampableBehavior($createdAt, new DateTime());

        $this->assertEquals('2023-03-15 14:25:30', $behavior->getCreatedAtFormatted());
    }

    public function testGetUpdatedAtFormattedWithCustomFormat(): void
    {
        $updatedAt = new DateTime('2023-04-20 09:15:45');
        $behavior = new TimestampableBehavior(new DateTime(), $updatedAt);

        $this->assertEquals('20/04/2023 09:15', $behavior->getUpdatedAtFormatted('d/m/Y H:i'));
    }

    public function testGetUpdatedAtFormattedWithDefaultFormat(): void
    {
        $updatedAt = new DateTime('2023-04-20 09:15:45');
        $behavior = new TimestampableBehavior(new DateTime(), $updatedAt);

        $this->assertEquals('2023-04-20 09:15:45', $behavior->getUpdatedAtFormatted());
    }

    public function testWasCreatedRecentlyReturnsFalseForOldDate(): void
    {
        $oldDate = (new DateTime())->modify('-2 days');
        $behavior = new TimestampableBehavior($oldDate, new DateTime());

        $this->assertFalse($behavior->wasCreatedRecently());
    }

    public function testWasCreatedRecentlyReturnsTrueForRecentDate(): void
    {
        $recentDate = (new DateTime())->modify('-12 hours');
        $behavior = new TimestampableBehavior($recentDate, new DateTime());

        $this->assertTrue($behavior->wasCreatedRecently());
    }

    public function testWasUpdatedRecentlyReturnsFalseForOldUpdate(): void
    {
        $oldDate = (new DateTime())->modify('-3 days');
        $behavior = new TimestampableBehavior(new DateTime(), $oldDate);

        $this->assertFalse($behavior->wasUpdatedRecently());
    }

    public function testWasUpdatedRecentlyReturnsTrueForRecentUpdate(): void
    {
        $recentDate = (new DateTime())->modify('-6 hours');
        $behavior = new TimestampableBehavior(new DateTime(), $recentDate);

        $this->assertTrue($behavior->wasUpdatedRecently());
    }
}
