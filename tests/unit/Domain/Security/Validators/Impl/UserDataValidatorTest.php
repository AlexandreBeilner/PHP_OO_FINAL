<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Security\Validators\Impl;

use App\Application\Shared\DTOs\Impl\ValidationResult;
use App\Domain\Security\Validators\Impl\UserDataValidator;
use PHPUnit\Framework\TestCase;

final class UserDataValidatorTest extends TestCase
{
    private UserDataValidator $validator;

    public function testValidateCreateUserDataWithEmptyData(): void
    {
        $result = $this->validator->validateCreateUserData([]);

        $this->assertFalse($result->isValid());
        $this->assertEquals(['dados' => 'Dados são obrigatórios'], $result->getErrors());
    }

    public function testValidateCreateUserDataWithInvalidFieldFormats(): void
    {
        $data = [
            'name' => 'A',  // Too short
            'email' => 'invalid-email',  // Invalid format
            'password' => '123',  // Too short
            'role' => 'invalid_role',  // Invalid role
        ];

        $result = $this->validator->validateCreateUserData($data);

        $this->assertFalse($result->isValid());
        $errors = $result->getErrors();

        $this->assertEquals('Nome deve ter pelo menos 2 caracteres', $errors['name']);
        $this->assertEquals('Email deve ter formato válido', $errors['email']);
        $this->assertEquals('Senha deve ter pelo menos 6 caracteres', $errors['password']);
        $this->assertEquals('Função inválida. Deve ser uma das: admin, user, moderator', $errors['role']);
    }

    public function testValidateCreateUserDataWithMinimumValidData(): void
    {
        $data = [
            'name' => 'Jo',  // Minimum 2 chars
            'email' => 'a@b.c',  // Valid minimal email
            'password' => '123456',  // Minimum 6 chars
        ];

        $result = $this->validator->validateCreateUserData($data);

        $this->assertTrue($result->isValid());
        $this->assertEquals([], $result->getErrors());
    }

    public function testValidateCreateUserDataWithMissingRequiredFields(): void
    {
        $data = ['role' => 'user']; // Missing required fields

        $result = $this->validator->validateCreateUserData($data);

        $this->assertFalse($result->isValid());
        $errors = $result->getErrors();

        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
        $this->assertEquals('Nome é obrigatório', $errors['name']);
        $this->assertEquals('Email é obrigatório', $errors['email']);
        $this->assertEquals('Senha é obrigatória', $errors['password']);
    }

    public function testValidateCreateUserDataWithValidData(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ];

        $result = $this->validator->validateCreateUserData($data);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertTrue($result->isValid());
        $this->assertEquals([], $result->getErrors());
    }

    public function testValidateCreateUserDataWithValidRoles(): void
    {
        $validRoles = ['admin', 'user', 'moderator'];

        foreach ($validRoles as $role) {
            $data = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'role' => $role,
            ];

            $result = $this->validator->validateCreateUserData($data);
            $this->assertTrue($result->isValid(), "Role '{$role}' should be valid");
        }
    }

    public function testValidateUpdateUserDataWithEmptyData(): void
    {
        $result = $this->validator->validateUpdateUserData([]);

        $this->assertFalse($result->isValid());
        $this->assertEquals(['dados' => 'Dados são obrigatórios'], $result->getErrors());
    }

    public function testValidateUpdateUserDataWithInvalidFields(): void
    {
        $data = [
            'name' => 'A',  // Too short
            'email' => 'invalid-email',
            'password' => '123',  // Too short
            'role' => 'invalid_role',
        ];

        $result = $this->validator->validateUpdateUserData($data);

        $this->assertFalse($result->isValid());
        $errors = $result->getErrors();

        $this->assertEquals('Nome deve ter pelo menos 2 caracteres', $errors['name']);
        $this->assertEquals('Email deve ter formato válido', $errors['email']);
        $this->assertEquals('Senha deve ter pelo menos 6 caracteres', $errors['password']);
        $this->assertEquals('Função inválida. Deve ser uma das: admin, user, moderator', $errors['role']);
    }

    public function testValidateUpdateUserDataWithValidData(): void
    {
        $data = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'moderator',
        ];

        $result = $this->validator->validateUpdateUserData($data);

        $this->assertTrue($result->isValid());
        $this->assertEquals([], $result->getErrors());
    }

    public function testValidateUserIdWithInvalidIds(): void
    {
        $invalidIds = [0, -1, -100];

        foreach ($invalidIds as $id) {
            $result = $this->validator->validateUserId($id);

            $this->assertFalse($result->isValid(), "ID {$id} should be invalid");
            $this->assertEquals(['id' => 'ID do usuário é obrigatório e deve ser um número positivo'], $result->getErrors());
        }
    }

    public function testValidateUserIdWithValidId(): void
    {
        $result = $this->validator->validateUserId(42);

        $this->assertTrue($result->isValid());
        $this->assertEquals([], $result->getErrors());
    }

    protected function setUp(): void
    {
        $this->validator = new UserDataValidator();
    }
}
