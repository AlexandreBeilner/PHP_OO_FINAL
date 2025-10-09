<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Security\Validators\Impl;

use App\Application\Shared\DTOs\Impl\ValidationResult;
use App\Domain\Security\Validators\Impl\AuthDataValidator;
use PHPUnit\Framework\TestCase;

final class AuthDataValidatorTest extends TestCase
{
    private AuthDataValidator $validator;

    public function testValidateAuthDataWithInvalidData(): void
    {
        $data = [
            'email' => 'invalid-email',
            'password' => '123',
        ];

        $result = $this->validator->validateAuthData($data);

        $this->assertFalse($result->isValid());
        $errors = $result->getErrors();

        $this->assertEquals('Email deve ter formato válido', $errors['email']);
        $this->assertEquals('Senha deve ter pelo menos 6 caracteres', $errors['password']);
    }

    public function testValidateAuthDataWithValidCredentials(): void
    {
        $data = [
            'email' => 'auth@example.com',
            'password' => 'authpass123',
        ];

        $result = $this->validator->validateAuthData($data);

        $this->assertTrue($result->isValid());
        $this->assertEquals([], $result->getErrors());
    }

    public function testValidateChangePasswordDataWithInvalidUserId(): void
    {
        $invalidIds = ['0', '-1', 'abc', ''];

        foreach ($invalidIds as $userId) {
            $data = [
                'user_id' => $userId,
                'current_password' => 'current123',
                'new_password' => 'new456',
            ];

            $result = $this->validator->validateChangePasswordData($data);

            $this->assertFalse($result->isValid(), "User ID '{$userId}' should be invalid");
            $this->assertArrayHasKey('user_id', $result->getErrors());
        }
    }

    public function testValidateChangePasswordDataWithMissingPasswords(): void
    {
        $data = ['user_id' => '10'];

        $result = $this->validator->validateChangePasswordData($data);

        $this->assertFalse($result->isValid());
        $errors = $result->getErrors();

        $this->assertEquals('Senha atual é obrigatória', $errors['current_password']);
        $this->assertEquals('Nova senha é obrigatória', $errors['new_password']);
    }

    public function testValidateChangePasswordDataWithMissingUserId(): void
    {
        $data = [
            'current_password' => 'current123',
            'new_password' => 'new456',
        ];

        $result = $this->validator->validateChangePasswordData($data);

        $this->assertFalse($result->isValid());
        $this->assertEquals(['user_id' => 'ID do usuário é obrigatório'], $result->getErrors());
    }

    public function testValidateChangePasswordDataWithValidData(): void
    {
        $data = [
            'user_id' => '42',
            'current_password' => 'currentpass123',
            'new_password' => 'newpassword456',
        ];

        $result = $this->validator->validateChangePasswordData($data);

        $this->assertTrue($result->isValid());
        $this->assertEquals([], $result->getErrors());
    }

    public function testValidateLoginDataWithInvalidEmail(): void
    {
        $data = [
            'email' => 'invalid-email-format',
            'password' => 'password123',
        ];

        $result = $this->validator->validateLoginData($data);

        $this->assertFalse($result->isValid());
        $this->assertEquals(['email' => 'Email deve ter formato válido'], $result->getErrors());
    }

    public function testValidateLoginDataWithMissingEmail(): void
    {
        $data = ['password' => 'password123'];

        $result = $this->validator->validateLoginData($data);

        $this->assertFalse($result->isValid());
        $this->assertEquals(['email' => 'Email é obrigatório'], $result->getErrors());
    }

    public function testValidateLoginDataWithShortPassword(): void
    {
        $data = [
            'email' => 'user@example.com',
            'password' => '123',
        ];

        $result = $this->validator->validateLoginData($data);

        $this->assertFalse($result->isValid());
        $this->assertEquals(['password' => 'Senha deve ter pelo menos 6 caracteres'], $result->getErrors());
    }

    public function testValidateLoginDataWithValidCredentials(): void
    {
        $data = [
            'email' => 'user@example.com',
            'password' => 'password123',
        ];

        $result = $this->validator->validateLoginData($data);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertTrue($result->isValid());
        $this->assertEquals([], $result->getErrors());
    }

    public function testValidateUserIdWithInvalidIds(): void
    {
        $invalidIds = [0, -1, -50];

        foreach ($invalidIds as $id) {
            $result = $this->validator->validateUserId($id);

            $this->assertFalse($result->isValid(), "ID {$id} should be invalid");
            $this->assertEquals(['id' => 'ID do usuário é obrigatório e deve ser um número positivo'], $result->getErrors());
        }
    }

    public function testValidateUserIdWithValidId(): void
    {
        $result = $this->validator->validateUserId(15);

        $this->assertTrue($result->isValid());
        $this->assertEquals([], $result->getErrors());
    }

    protected function setUp(): void
    {
        $this->validator = new AuthDataValidator();
    }
}
