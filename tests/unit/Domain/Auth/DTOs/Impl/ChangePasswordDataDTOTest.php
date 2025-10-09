<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Auth\DTOs\Impl;

use App\Domain\Auth\DTOs\Impl\ChangePasswordDataDTO;
use PHPUnit\Framework\TestCase;

final class ChangePasswordDataDTOTest extends TestCase
{
    public function testConstructorSetsAllProperties(): void
    {
        $userId = 42;
        $currentPassword = 'currentpass123';
        $newPassword = 'newpass456';

        $dto = new ChangePasswordDataDTO($userId, $currentPassword, $newPassword);

        $this->assertEquals($userId, $dto->userId);
        $this->assertEquals($currentPassword, $dto->currentPassword);
        $this->assertEquals($newPassword, $dto->newPassword);
    }

    public function testConstructorWithZeroUserId(): void
    {
        $dto = new ChangePasswordDataDTO(0, 'current', 'new');

        $this->assertEquals(0, $dto->userId);
        $this->assertEquals('current', $dto->currentPassword);
        $this->assertEquals('new', $dto->newPassword);
    }

    public function testFromArrayPrefersUnderscoreOverCamelCase(): void
    {
        $data = [
            'user_id' => 100,       // This should win (comes first in ?? chain)
            'userId' => 200,
            'current_password' => 'underscore',  // This should win
            'currentPassword' => 'camelcase',
            'new_password' => 'underscore_new',  // This should win
            'newPassword' => 'camelcase_new',
        ];

        $dto = ChangePasswordDataDTO::fromArray($data);

        $this->assertEquals(100, $dto->userId);
        $this->assertEquals('underscore', $dto->currentPassword);
        $this->assertEquals('underscore_new', $dto->newPassword);
    }

    public function testFromArrayWithEmptyArray(): void
    {
        $dto = ChangePasswordDataDTO::fromArray([]);

        $this->assertEquals(0, $dto->userId);
        $this->assertEquals('', $dto->currentPassword);
        $this->assertEquals('', $dto->newPassword);
    }

    public function testFromArrayWithExtraFields(): void
    {
        $data = [
            'userId' => 30,
            'currentPassword' => 'current',
            'newPassword' => 'new',
            'extra_field' => 'ignored',
            'confirmPassword' => 'ignored_too',
            'timestamp' => '2025-01-01',
        ];

        $dto = ChangePasswordDataDTO::fromArray($data);

        $this->assertEquals(30, $dto->userId);
        $this->assertEquals('current', $dto->currentPassword);
        $this->assertEquals('new', $dto->newPassword);
        // Extra fields are ignored
    }

    public function testFromArrayWithMissingValues(): void
    {
        $data = [
            'userId' => 15,
            // Missing passwords
        ];

        $dto = ChangePasswordDataDTO::fromArray($data);

        $this->assertEquals(15, $dto->userId);
        $this->assertEquals('', $dto->currentPassword);
        $this->assertEquals('', $dto->newPassword);
    }

    public function testFromArrayWithNullValues(): void
    {
        $data = [
            'userId' => null,
            'currentPassword' => null,
            'newPassword' => null,
        ];

        $dto = ChangePasswordDataDTO::fromArray($data);

        $this->assertEquals(0, $dto->userId);
        $this->assertEquals('', $dto->currentPassword);
        $this->assertEquals('', $dto->newPassword);
    }

    public function testFromArrayWithStringUserId(): void
    {
        $data = [
            'userId' => '123',
            'currentPassword' => 'current',
            'newPassword' => 'new',
        ];

        $dto = ChangePasswordDataDTO::fromArray($data);

        $this->assertEquals(123, $dto->userId);
        $this->assertEquals('current', $dto->currentPassword);
        $this->assertEquals('new', $dto->newPassword);
        $this->assertIsInt($dto->userId);
    }

    public function testFromArrayWithUnderscoreKeys(): void
    {
        $data = [
            'user_id' => 25,
            'current_password' => 'currentpass',
            'new_password' => 'newpass',
        ];

        $dto = ChangePasswordDataDTO::fromArray($data);

        $this->assertEquals(25, $dto->userId);
        $this->assertEquals('currentpass', $dto->currentPassword);
        $this->assertEquals('newpass', $dto->newPassword);
    }

    public function testFromArrayWithUserIdKey(): void
    {
        $data = [
            'userId' => 10,
            'currentPassword' => 'old123',
            'newPassword' => 'new456',
        ];

        $dto = ChangePasswordDataDTO::fromArray($data);

        $this->assertEquals(10, $dto->userId);
        $this->assertEquals('old123', $dto->currentPassword);
        $this->assertEquals('new456', $dto->newPassword);
    }

    public function testPropertiesArePublic(): void
    {
        $dto = new ChangePasswordDataDTO(5, 'current', 'new');

        // Verificar que as propriedades são públicas e podem ser acessadas
        $this->assertTrue(property_exists($dto, 'userId'));
        $this->assertTrue(property_exists($dto, 'currentPassword'));
        $this->assertTrue(property_exists($dto, 'newPassword'));

        // Verificar que podem ser modificadas diretamente
        $dto->userId = 99;
        $dto->currentPassword = 'modified_current';
        $dto->newPassword = 'modified_new';

        $this->assertEquals(99, $dto->userId);
        $this->assertEquals('modified_current', $dto->currentPassword);
        $this->assertEquals('modified_new', $dto->newPassword);
    }
}
