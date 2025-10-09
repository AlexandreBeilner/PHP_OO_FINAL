<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Security\DTOs\Impl;

use App\Domain\Security\DTOs\Impl\UpdateUserDataDTO;
use PHPUnit\Framework\TestCase;

final class UpdateUserDataDTOTest extends TestCase
{
    public function testConstructorWithAllParameters(): void
    {
        $name = 'John Updated';
        $email = 'john.updated@example.com';
        $password = 'newpassword123';
        $role = 'admin';
        $status = 'inactive';

        $dto = new UpdateUserDataDTO($name, $email, $password, $role, $status);

        $this->assertEquals($name, $dto->name);
        $this->assertEquals($email, $dto->email);
        $this->assertEquals($password, $dto->password);
        $this->assertEquals($role, $dto->role);
        $this->assertEquals($status, $dto->status);
    }

    public function testConstructorWithNullValues(): void
    {
        $dto = new UpdateUserDataDTO();

        $this->assertNull($dto->name);
        $this->assertNull($dto->email);
        $this->assertNull($dto->password);
        $this->assertNull($dto->role);
        $this->assertNull($dto->status);
    }

    public function testConstructorWithPartialValues(): void
    {
        $dto = new UpdateUserDataDTO('Updated Name', null, null, 'moderator');

        $this->assertEquals('Updated Name', $dto->name);
        $this->assertNull($dto->email);
        $this->assertNull($dto->password);
        $this->assertEquals('moderator', $dto->role);
        $this->assertNull($dto->status);
    }

    public function testFromArrayWithCompleteData(): void
    {
        $data = [
            'name' => 'Alice Updated',
            'email' => 'alice.new@test.com',
            'password' => 'newsecret',
            'role' => 'admin',
            'status' => 'active',
        ];

        $dto = UpdateUserDataDTO::fromArray($data);

        $this->assertEquals('Alice Updated', $dto->name);
        $this->assertEquals('alice.new@test.com', $dto->email);
        $this->assertEquals('newsecret', $dto->password);
        $this->assertEquals('admin', $dto->role);
        $this->assertEquals('active', $dto->status);
    }

    public function testFromArrayWithEmptyArray(): void
    {
        $dto = UpdateUserDataDTO::fromArray([]);

        $this->assertNull($dto->name);
        $this->assertNull($dto->email);
        $this->assertNull($dto->password);
        $this->assertNull($dto->role);
        $this->assertNull($dto->status);
    }

    public function testFromArrayWithEmptyStringsVsNull(): void
    {
        $data = [
            'name' => '',
            'email' => '',
            'password' => '',
            'role' => '',
            'status' => '',
        ];

        $dto = UpdateUserDataDTO::fromArray($data);

        // Empty strings are preserved (not converted to null)
        $this->assertEquals('', $dto->name);
        $this->assertEquals('', $dto->email);
        $this->assertEquals('', $dto->password);
        $this->assertEquals('', $dto->role);
        $this->assertEquals('', $dto->status);
    }

    public function testFromArrayWithMissingFields(): void
    {
        $data = [
            'name' => 'Bob Partial',
            'role' => 'user',
        ];

        $dto = UpdateUserDataDTO::fromArray($data);

        $this->assertEquals('Bob Partial', $dto->name);
        $this->assertNull($dto->email);
        $this->assertNull($dto->password);
        $this->assertEquals('user', $dto->role);
        $this->assertNull($dto->status);
    }

    public function testFromArrayWithNullExplicitValues(): void
    {
        $data = [
            'name' => null,
            'email' => null,
            'password' => null,
            'role' => null,
            'status' => null,
        ];

        $dto = UpdateUserDataDTO::fromArray($data);

        $this->assertNull($dto->name);
        $this->assertNull($dto->email);
        $this->assertNull($dto->password);
        $this->assertNull($dto->role);
        $this->assertNull($dto->status);
    }

    public function testPropertiesArePublic(): void
    {
        $dto = new UpdateUserDataDTO('Test', 'test@example.com');

        // Verificar que as propriedades são públicas e podem ser acessadas
        $this->assertTrue(property_exists($dto, 'name'));
        $this->assertTrue(property_exists($dto, 'email'));
        $this->assertTrue(property_exists($dto, 'password'));
        $this->assertTrue(property_exists($dto, 'role'));
        $this->assertTrue(property_exists($dto, 'status'));

        // Verificar que podem ser modificadas diretamente (herda do pai)
        $dto->name = 'Modified Name';
        $dto->status = 'inactive';

        $this->assertEquals('Modified Name', $dto->name);
        $this->assertEquals('inactive', $dto->status);
    }

    public function testToArrayWithAllValues(): void
    {
        $dto = new UpdateUserDataDTO('Test User', 'test@example.com', 'testpass', 'admin', 'active');
        $array = $dto->toArray();

        $expected = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'testpass',
            'role' => 'admin',
            'status' => 'active',
        ];

        $this->assertEquals($expected, $array);
    }

    public function testToArrayWithNullValues(): void
    {
        $dto = new UpdateUserDataDTO('Only Name', null, null, null, null);
        $array = $dto->toArray();

        // Only non-null values should be in the array
        $this->assertEquals(['name' => 'Only Name'], $array);
    }

    public function testToArrayWithPartialValues(): void
    {
        $dto = new UpdateUserDataDTO(null, 'newemail@test.com', null, 'moderator', null);
        $array = $dto->toArray();

        $expected = [
            'email' => 'newemail@test.com',
            'role' => 'moderator',
        ];

        $this->assertEquals($expected, $array);
    }
}
