<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Auth\DTOs\Impl;

use App\Domain\Auth\DTOs\Impl\LoginDataDTO;
use PHPUnit\Framework\TestCase;

final class LoginDataDTOTest extends TestCase
{
    public function testConstructorSetsEmailAndPassword(): void
    {
        $email = 'user@example.com';
        $password = 'secret123';

        $dto = new LoginDataDTO($email, $password);

        $this->assertEquals($email, $dto->email);
        $this->assertEquals($password, $dto->password);
    }

    public function testConstructorWithEmptyValues(): void
    {
        $dto = new LoginDataDTO('', '');

        $this->assertEquals('', $dto->email);
        $this->assertEquals('', $dto->password);
    }

    public function testFromArrayPreservesOriginalCase(): void
    {
        $data = [
            'email' => 'User@Example.COM',
            'password' => 'Password123!',
        ];

        $dto = LoginDataDTO::fromArray($data);

        $this->assertEquals('User@Example.COM', $dto->email);
        $this->assertEquals('Password123!', $dto->password);
    }

    public function testFromArrayWithEmptyArray(): void
    {
        $dto = LoginDataDTO::fromArray([]);

        $this->assertEquals('', $dto->email);
        $this->assertEquals('', $dto->password);
    }

    public function testFromArrayWithExtraData(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember_me' => true,
            'extra_field' => 'ignored',
        ];

        $dto = LoginDataDTO::fromArray($data);

        $this->assertEquals('test@example.com', $dto->email);
        $this->assertEquals('password123', $dto->password);
    }

    public function testFromArrayWithMissingEmail(): void
    {
        $data = ['password' => 'test123'];

        $dto = LoginDataDTO::fromArray($data);

        $this->assertEquals('', $dto->email);
        $this->assertEquals('test123', $dto->password);
    }

    public function testFromArrayWithMissingPassword(): void
    {
        $data = ['email' => 'user@example.com'];

        $dto = LoginDataDTO::fromArray($data);

        $this->assertEquals('user@example.com', $dto->email);
        $this->assertEquals('', $dto->password);
    }

    public function testFromArrayWithNullValues(): void
    {
        $data = [
            'email' => null,
            'password' => null,
        ];

        $dto = LoginDataDTO::fromArray($data);

        $this->assertEquals('', $dto->email);
        $this->assertEquals('', $dto->password);
    }

    public function testFromArrayWithValidData(): void
    {
        $data = [
            'email' => 'admin@test.com',
            'password' => 'admin123',
        ];

        $dto = LoginDataDTO::fromArray($data);

        $this->assertEquals('admin@test.com', $dto->email);
        $this->assertEquals('admin123', $dto->password);
    }

    public function testPropertiesArePublic(): void
    {
        $dto = new LoginDataDTO('test@example.com', 'password');

        // Verificar que as propriedades são públicas e podem ser acessadas
        $this->assertTrue(property_exists($dto, 'email'));
        $this->assertTrue(property_exists($dto, 'password'));

        // Verificar que podem ser modificadas diretamente
        $dto->email = 'modified@example.com';
        $dto->password = 'newpassword';

        $this->assertEquals('modified@example.com', $dto->email);
        $this->assertEquals('newpassword', $dto->password);
    }
}
