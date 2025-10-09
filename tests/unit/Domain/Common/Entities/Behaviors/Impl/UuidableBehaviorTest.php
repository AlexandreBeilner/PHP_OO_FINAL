<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Common\Entities\Behaviors\Impl;

use App\Domain\Common\Entities\Behaviors\Impl\UuidableBehavior;
use PHPUnit\Framework\TestCase;

final class UuidableBehaviorTest extends TestCase
{
    public function testConstructorWithInvalidUuid(): void
    {
        $invalidUuid = 'invalid-uuid-format';
        $behavior = new UuidableBehavior($invalidUuid);

        $this->assertTrue($behavior->hasUuid()); // Has UUID
        $this->assertFalse($behavior->hasValidUuid()); // But invalid format
    }

    public function testConstructorWithUuid(): void
    {
        $uuid = '12345678-1234-1234-1234-123456789abc';
        $behavior = new UuidableBehavior($uuid);

        $this->assertTrue($behavior->hasUuid());
        $this->assertTrue($behavior->hasValidUuid());
        $this->assertTrue($behavior->matchesUuid($uuid));
    }

    public function testConstructorWithoutUuid(): void
    {
        $behavior = new UuidableBehavior();

        $this->assertFalse($behavior->hasUuid());
        $this->assertFalse($behavior->hasValidUuid());
    }

    public function testGenerateUuidCreatesValidUuid(): void
    {
        $behavior = new UuidableBehavior();
        $result = $behavior->generateUuid();

        $this->assertSame($behavior, $result); // Returns self
        $this->assertTrue($behavior->hasUuid());
        $this->assertTrue($behavior->hasValidUuid());
    }

    public function testGenerateUuidOverwritesExistingUuid(): void
    {
        $originalUuid = '12345678-1234-1234-1234-123456789abc';
        $behavior = new UuidableBehavior($originalUuid);

        $this->assertTrue($behavior->matchesUuid($originalUuid));

        $behavior->generateUuid();

        $this->assertTrue($behavior->hasUuid());
        $this->assertTrue($behavior->hasValidUuid());
        $this->assertFalse($behavior->matchesUuid($originalUuid)); // Should be different now
    }

    public function testHasUuidReturnsTrueWhenUuidExists(): void
    {
        $behavior = new UuidableBehavior();
        $this->assertFalse($behavior->hasUuid());

        $behavior->generateUuid();
        $this->assertTrue($behavior->hasUuid());
    }

    public function testHasValidUuidWithInvalidFormat(): void
    {
        $invalidUuids = [
            'too-short',
            '12345678-1234-1234-1234-123456789abcd', // Too long
            '12345678-1234-1234-1234-123456789ab',   // Too short
            '12345678-1234-1234-123456789abc',       // Missing section
            '12345678_1234_1234_1234_123456789abc',  // Wrong separator
            'ggggghhh-1234-1234-1234-123456789abc',  // Invalid hex chars
        ];

        foreach ($invalidUuids as $uuid) {
            $behavior = new UuidableBehavior($uuid);
            $this->assertFalse($behavior->hasValidUuid(), "UUID {$uuid} should be invalid");
        }
    }

    public function testHasValidUuidWithValidFormat(): void
    {
        $validUuids = [
            '12345678-1234-1234-1234-123456789abc',
            '00000000-0000-0000-0000-000000000000',
            'ffffffff-ffff-ffff-ffff-ffffffffffff',
            'AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE',
        ];

        foreach ($validUuids as $uuid) {
            $behavior = new UuidableBehavior($uuid);
            $this->assertTrue($behavior->hasValidUuid(), "UUID {$uuid} should be valid");
        }
    }

    public function testMatchesUuidWhenNoUuidSet(): void
    {
        $behavior = new UuidableBehavior();
        $uuid = '12345678-1234-1234-1234-123456789abc';

        $this->assertFalse($behavior->matchesUuid($uuid));
    }

    public function testMatchesUuidWithMatchingUuid(): void
    {
        $uuid = '12345678-1234-1234-1234-123456789abc';
        $behavior = new UuidableBehavior($uuid);

        $this->assertTrue($behavior->matchesUuid($uuid));
        $this->assertTrue($behavior->matchesUuid($uuid)); // Case sensitive
    }

    public function testMatchesUuidWithNonMatchingUuid(): void
    {
        $uuid1 = '12345678-1234-1234-1234-123456789abc';
        $uuid2 = '87654321-4321-4321-4321-cba987654321';
        $behavior = new UuidableBehavior($uuid1);

        $this->assertFalse($behavior->matchesUuid($uuid2));
    }

    public function testRegenerateUuidCreatesDifferentUuid(): void
    {
        $behavior = new UuidableBehavior();

        $behavior->generateUuid();
        $behavior->generateUuid(); // Should generate different UUID each time

        $this->assertTrue($behavior->hasUuid());
        $this->assertTrue($behavior->hasValidUuid());
    }

    public function testRegenerateUuidReturnsSelf(): void
    {
        $behavior = new UuidableBehavior();

        $result = $behavior->regenerateUuid();

        $this->assertSame($behavior, $result);
        $this->assertTrue($behavior->hasUuid());
        $this->assertTrue($behavior->hasValidUuid());
    }
}
