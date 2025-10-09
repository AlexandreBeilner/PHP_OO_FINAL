<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Security\DTOs\Impl;

use App\Domain\Security\DTOs\Impl\CreateUserDataDTO;
use PHPUnit\Framework\TestCase;

final class CreateUserDataDTOTest extends TestCase
{
    public function testConstructorWithAllParameters(): void
    {
        $name = 'John Doe';
        $email = 'john@example.com';
        $password = 'password123';
        $role = 'admin';

        $dto = new CreateUserDataDTO($name, $email, $password, $role);

        $this->assertEquals($name, $dto->name);
        $this->assertEquals($email, $dto->email);
        $this->assertEquals($password, $dto->password);
        $this->assertEquals($role, $dto->role);
    }

    public function testConstructorWithDefaultRole(): void
    {
        $name = 'Jane Doe';
        $email = 'jane@example.com';
        $password = 'password456';

        $dto = new CreateUserDataDTO($name, $email, $password);

        $this->assertEquals($name, $dto->name);
        $this->assertEquals($email, $dto->email);
        $this->assertEquals($password, $dto->password);
        $this->assertEquals('user', $dto->role);
    }

    public function testFromArrayWithCompleteData(): void
    {
        $data = [
            'name' => 'Alice Johnson',
            'email' => 'alice@test.com',
            'password' => 'securepass',
            'role' => 'moderator',
        ];

        $dto = CreateUserDataDTO::fromArray($data);

        $this->assertEquals('Alice Johnson', $dto->name);
        $this->assertEquals('alice@test.com', $dto->email);
        $this->assertEquals('securepass', $dto->password);
        $this->assertEquals('moderator', $dto->role);
    }

    public function testFromArrayWithEmptyArray(): void
    {
        $dto = CreateUserDataDTO::fromArray([]);

        $this->assertEquals('', $dto->name);
        $this->assertEquals('', $dto->email);
        $this->assertEquals('', $dto->password);
        $this->assertEquals('user', $dto->role);
    }

    public function testFromArrayWithEmptyValues(): void
    {
        $data = [
            'name' => '',
            'email' => '',
            'password' => '',
            'role' => '',
        ];

        $dto = CreateUserDataDTO::fromArray($data);

        $this->assertEquals('', $dto->name);
        $this->assertEquals('', $dto->email);
        $this->assertEquals('', $dto->password);
        $this->assertEquals('', $dto->role);
    }

    public function testFromArrayWithExtraFields(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'testpass',
            'role' => 'admin',
            'extra_field' => 'ignored',
            'id' => 123,
            'created_at' => '2025-01-01',
        ];

        $dto = CreateUserDataDTO::fromArray($data);

        $this->assertEquals('Test User', $dto->name);
        $this->assertEquals('test@example.com', $dto->email);
        $this->assertEquals('testpass', $dto->password);
        $this->assertEquals('admin', $dto->role);
    }

    public function testFromArrayWithMissingRole(): void
    {
        $data = [
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'password' => 'password789',
        ];

        $dto = CreateUserDataDTO::fromArray($data);

        $this->assertEquals('Bob Smith', $dto->name);
        $this->assertEquals('bob@example.com', $dto->email);
        $this->assertEquals('password789', $dto->password);
        $this->assertEquals('user', $dto->role);
    }

    public function testFromArrayWithMissingValues(): void
    {
        $data = [
            'email' => 'test@example.com',
        ];

        $dto = CreateUserDataDTO::fromArray($data);

        $this->assertEquals('', $dto->name);
        $this->assertEquals('test@example.com', $dto->email);
        $this->assertEquals('', $dto->password);
        $this->assertEquals('user', $dto->role);
    }

    public function testFromArrayWithNullValues(): void
    {
        $data = [
            'name' => null,
            'email' => null,
            'password' => null,
            'role' => null,
        ];

        $dto = CreateUserDataDTO::fromArray($data);

        $this->assertEquals('', $dto->name);
        $this->assertEquals('', $dto->email);
        $this->assertEquals('', $dto->password);
        $this->assertEquals('user', $dto->role);
    }

    public function testPropertiesArePublic(): void
    {
        $dto = new CreateUserDataDTO('Test', 'test@example.com', 'password', 'user');

        // Verificar que as propriedades são públicas e podem ser acessadas
        $this->assertTrue(property_exists($dto, 'name'));
        $this->assertTrue(property_exists($dto, 'email'));
        $this->assertTrue(property_exists($dto, 'password'));
        $this->assertTrue(property_exists($dto, 'role'));

        // Verificar que podem ser modificadas diretamente (herda do pai)
        $dto->name = 'Modified Name';
        $dto->email = 'modified@example.com';

        $this->assertEquals('Modified Name', $dto->name);
        $this->assertEquals('modified@example.com', $dto->email);
    }
}
