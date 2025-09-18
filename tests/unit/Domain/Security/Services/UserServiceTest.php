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
        $name = 'John Doe';
        $email = 'john@example.com';
        $password = 'password123';
        $role = 'user';

        $this->mockEmailValidator->expects($this->once())
            ->method('validate')
            ->with($email)
            ->willReturn(true);

        $this->mockRepository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null);

        $this->mockRepository->expects($this->once())
            ->method('save')
            ->willReturnCallback(function ($user) {
                $this->assertInstanceOf(UserEntity::class, $user);
                $this->assertEquals('John Doe', $user->getName());
                $this->assertEquals('john@example.com', $user->getEmail());
                $this->assertEquals('user', $user->getRole());
                $this->assertEquals('active', $user->getStatus());
                return $user;
            });

        $result = $this->userService->createUser($name, $email, $password, $role);

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

        $this->userService->createUser('John Doe', 'invalid-email', 'password123', 'user');
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

        $this->userService->createUser('John Doe', 'john@example.com', 'password123', 'user');
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

        $this->userService->createUser('John Doe', 'john@example.com', '123', 'user');
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

        $this->userService->createUser('John Doe', 'john@example.com', 'password123', 'invalid_role');
    }

    public function testGetUserById(): void
    {
        $user = $this->createMock(UserEntityInterface::class);
        
        $this->mockRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($user);

        $result = $this->userService->getUserById(1);
        $this->assertSame($user, $result);
    }

    public function testGetUserByIdReturnsNullWhenNotFound(): void
    {
        $this->mockRepository->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $result = $this->userService->getUserById(999);
        $this->assertNull($result);
    }

    public function testGetUserByEmail(): void
    {
        $user = $this->createMock(UserEntityInterface::class);
        
        $this->mockRepository->expects($this->once())
            ->method('findByEmail')
            ->with('john@example.com')
            ->willReturn($user);

        $result = $this->userService->getUserByEmail('john@example.com');
        $this->assertSame($user, $result);
    }

    public function testUpdateUser(): void
    {
        $user = $this->createMock(UserEntityInterface::class);
        $user->expects($this->once())->method('setName')->with('John Updated');
        $user->expects($this->once())->method('setRole')->with('admin');
        $user->expects($this->once())->method('touch');

        $this->mockRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($user);

        $this->mockRepository->expects($this->once())
            ->method('save')
            ->with($user)
            ->willReturn($user);

        $result = $this->userService->updateUser(1, [
            'name' => 'John Updated',
            'role' => 'admin'
        ]);

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

        $this->userService->updateUser(999, ['name' => 'Updated Name']);
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
        $user->expects($this->once())->method('getPassword')->willReturn(password_hash('password123', PASSWORD_DEFAULT));
        
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
        $user->expects($this->once())->method('getPassword')->willReturn(password_hash('password123', PASSWORD_DEFAULT));
        
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

        $result = $this->userService->isEmailAvailable('available@example.com');
        $this->assertTrue($result);
    }

    public function testIsEmailAvailableReturnsFalseWhenEmailExists(): void
    {
        $user = $this->createMock(UserEntityInterface::class);
        
        $this->mockRepository->expects($this->once())
            ->method('findByEmail')
            ->with('taken@example.com')
            ->willReturn($user);

        $result = $this->userService->isEmailAvailable('taken@example.com');
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

        $result = $this->userService->isEmailAvailable('john@example.com', 1);
        $this->assertTrue($result);
    }
}
