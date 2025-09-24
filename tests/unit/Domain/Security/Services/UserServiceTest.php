<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Users\Services\Impl;

use PHPUnit\Framework\TestCase;
use App\Domain\Security\Services\Impl\UserService;
use App\Domain\Security\Repositories\UserRepositoryInterface;
use App\Domain\Common\Validators\EmailValidatorInterface;
use App\Domain\Common\Exceptions\Impl\ValidationException;
use App\Domain\Common\Exceptions\Impl\BusinessLogicExceptionAbstract;
use App\Domain\Security\Entities\UserEntityInterface;
use App\Domain\Security\Entities\Impl\UserEntity;
use App\Domain\Security\DTOs\Impl\CreateUserDataDTO;
use App\Domain\Security\DTOs\Impl\UpdateUserDataDTO;
use App\Domain\Common\Impl\TimestampableBehavior;
use App\Domain\Common\Impl\UuidableBehavior;

final class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepositoryInterface $mockRepository;
    private EmailValidatorInterface $mockEmailValidator;

    protected function setUp(): void
    {
        $this->mockRepository = $this->createMock(UserRepositoryInterface::class);
        $this->mockEmailValidator = $this->createMock(EmailValidatorInterface::class);
        
        $this->userService = new UserService(
            $this->mockRepository,
            $this->mockEmailValidator
        );
    }

    public function testCreateUserSuccessfully(): void
    {
        $dto = CreateUserDataDTO::fromArray([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => 'user'
        ]);

        $this->mockEmailValidator->expects($this->once())
            ->method('validate')
            ->with('john@example.com')
            ->willReturn(true);

        $this->mockRepository->expects($this->once())
            ->method('findByEmail')
            ->with('john@example.com')
            ->willReturn(null);

        $this->mockRepository->expects($this->once())
            ->method('save')
            ->willReturnCallback(function ($user) {
                $this->assertInstanceOf(UserEntity::class, $user);
                $this->assertEquals('John Doe', $user->name);
                $this->assertEquals('john@example.com', $user->email);
                $this->assertEquals('user', $user->role);
                $this->assertEquals('active', $user->status);
                return $user;
            });

        $result = $this->userService->createUser($dto);

        $this->assertInstanceOf(UserEntityInterface::class, $result);
    }

    public function testCreateUserWithInvalidEmail(): void
    {
        $this->mockEmailValidator->expects($this->once())
            ->method('validate')
            ->with('invalid-email')
            ->willReturn(false);

        $this->mockEmailValidator->expects($this->once())
            ->method('getErrorMessage')
            ->willReturn('Invalid email format');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid email format');

        $dto = CreateUserDataDTO::fromArray([
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'role' => 'user'
        ]);

        $this->userService->createUser($dto);
    }

    public function testCreateUserWithDuplicateEmail(): void
    {
        $existingUser = $this->createMock(UserEntityInterface::class);
        
        $this->mockEmailValidator->expects($this->once())
            ->method('validate')
            ->with('john@example.com')
            ->willReturn(true);

        $this->mockRepository->expects($this->once())
            ->method('findByEmail')
            ->with('john@example.com')
            ->willReturn($existingUser);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Email \'john@example.com\' já está em uso');

        $dto = CreateUserDataDTO::fromArray([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => 'user'
        ]);

        $this->userService->createUser($dto);
    }

    public function testCreateUserWithShortPassword(): void
    {
        $this->mockEmailValidator->expects($this->once())
            ->method('validate')
            ->with('john@example.com')
            ->willReturn(true);

        $this->mockRepository->expects($this->once())
            ->method('findByEmail')
            ->with('john@example.com')
            ->willReturn(null);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Senha deve ter pelo menos 6 caracteres');

        $dto = CreateUserDataDTO::fromArray([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '123',
            'role' => 'user'
        ]);

        $this->userService->createUser($dto);
    }

    public function testCreateUserWithInvalidRole(): void
    {
        $this->mockEmailValidator->expects($this->once())
            ->method('validate')
            ->with('john@example.com')
            ->willReturn(true);

        $this->mockRepository->expects($this->once())
            ->method('findByEmail')
            ->with('john@example.com')
            ->willReturn(null);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Função inválida. Deve ser uma das: admin, user, moderator');

        $dto = CreateUserDataDTO::fromArray([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => 'invalid_role'
        ]);

        $this->userService->createUser($dto);
    }

    public function testGetUserById(): void
    {
        $user = $this->createMock(UserEntityInterface::class);
        
        $this->mockRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($user);

        $result = $this->userService->processUserById(1, fn($user) => $user);
        $this->assertSame($user, $result);
    }

    public function testGetUserByIdReturnsNullWhenNotFound(): void
    {
        $this->mockRepository->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        // Verificar que exceção é lançada quando usuário não encontrado
        $this->expectException(\App\Domain\Common\Exceptions\Impl\BusinessLogicExceptionAbstract::class);
        $this->userService->processUserById(999, fn($user) => $user);
    }

    public function testGetUserByEmail(): void
    {
        $user = $this->createMock(UserEntityInterface::class);
        
        $user->expects($this->once())
            ->method('authenticate')
            ->with('correct-password')
            ->willReturn(true);

        $this->mockRepository->expects($this->once())
            ->method('findByEmail')
            ->with('john@example.com')
            ->willReturn($user);

        // Como getUserByEmail foi removido, vamos testar authenticateUserByEmail
        $result = $this->userService->authenticateUserByEmail('john@example.com', 'correct-password');
        $this->assertSame($user, $result);
    }

    public function testUpdateUser(): void
    {
        $user = $this->createMock(UserEntityInterface::class);
        $user->expects($this->once())->method('updateProfile')->with(['name' => 'John Updated', 'role' => 'admin']);

        $this->mockRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($user);

        $this->mockRepository->expects($this->once())
            ->method('save')
            ->with($user)
            ->willReturn($user);

        $dto = UpdateUserDataDTO::fromArray([
            'name' => 'John Updated',
            'role' => 'admin'
        ]);

        $result = $this->userService->updateUser(1, $dto);

        $this->assertSame($user, $result);
    }

    public function testUpdateNonExistentUser(): void
    {
        $this->mockRepository->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(BusinessLogicExceptionAbstract::class);
        $this->expectExceptionMessage('Usuário com ID 999 não encontrado');

        $dto = UpdateUserDataDTO::fromArray([
            'name' => 'Updated Name'
        ]);

        $this->userService->updateUser(999, $dto);
    }

    public function testDeleteUser(): void
    {
        $user = $this->createMock(UserEntityInterface::class);
        
        $this->mockRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($user);

        $this->mockRepository->expects($this->once())
            ->method('delete')
            ->with($user)
            ->willReturn(true);

        $result = $this->userService->deleteUser(1);
        $this->assertTrue($result);
    }

    public function testDeleteUserReturnsFalseWhenNotFound(): void
    {
        $this->mockRepository->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $result = $this->userService->deleteUser(999);
        $this->assertFalse($result);
    }

    public function testAuthenticateUser(): void
    {
        $user = $this->createMock(UserEntityInterface::class);
        $user->expects($this->once())->method('authenticate')->with('password123')->willReturn(true);
        
        $this->mockRepository->expects($this->once())
            ->method('findByEmail')
            ->with('john@example.com')
            ->willReturn($user);

        $result = $this->userService->authenticateUser('john@example.com', 'password123');
        $this->assertSame($user, $result);
    }

    public function testAuthenticateUserWithWrongPassword(): void
    {
        $user = $this->createMock(UserEntityInterface::class);
        $user->expects($this->once())->method('authenticate')->with('wrongpassword')->willReturn(false);
        
        $this->mockRepository->expects($this->once())
            ->method('findByEmail')
            ->with('john@example.com')
            ->willReturn($user);

        $result = $this->userService->authenticateUser('john@example.com', 'wrongpassword');
        $this->assertNull($result);
    }

    public function testAuthenticateUserWithNonExistentEmail(): void
    {
        $this->mockRepository->expects($this->once())
            ->method('findByEmail')
            ->with('nonexistent@example.com')
            ->willReturn(null);

        $result = $this->userService->authenticateUser('nonexistent@example.com', 'password123');
        $this->assertNull($result);
    }

    public function testIsEmailAvailable(): void
    {
        $this->mockRepository->expects($this->once())
            ->method('findByEmail')
            ->with('available@example.com')
            ->willReturn(null);

        $result = $this->userService->validateEmailAvailability('available@example.com');
        $this->assertTrue($result);
    }

    public function testIsEmailAvailableReturnsFalseWhenEmailExists(): void
    {
        $user = $this->createMock(UserEntityInterface::class);
        
        $this->mockRepository->expects($this->once())
            ->method('findByEmail')
            ->with('taken@example.com')
            ->willReturn($user);

        $result = $this->userService->validateEmailAvailability('taken@example.com');
        $this->assertFalse($result);
    }

    public function testIsEmailAvailableExcludesCurrentUser(): void
    {
        $user = $this->createMock(UserEntityInterface::class);
        $user->expects($this->once())->method('getId')->willReturn(1);
        
        $this->mockRepository->expects($this->once())
            ->method('findByEmail')
            ->with('john@example.com')
            ->willReturn($user);

        $result = $this->userService->validateEmailAvailability('john@example.com', 1);
        $this->assertTrue($result);
    }
}
