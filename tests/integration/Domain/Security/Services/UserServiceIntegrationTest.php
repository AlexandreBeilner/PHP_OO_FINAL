<?php

declare(strict_types=1);

namespace Tests\Integration\Domain\Security\Services;

use Tests\Integration\AbstractBaseIntegrationTest;
use App\Domain\Security\Services\UserServiceInterface;
use App\Domain\Security\Entities\UserEntityInterface;
use App\Domain\Security\DTOs\Impl\CreateUserDataDTO;
use App\Domain\Security\DTOs\Impl\UpdateUserDataDTO;
use App\Domain\Common\Exceptions\Impl\ValidationException;
use App\Domain\Common\Exceptions\Impl\BusinessLogicExceptionAbstract;

final class UserServiceIntegrationTest extends AbstractBaseIntegrationTest
{
    private UserServiceInterface $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = $this->app->container()->get(UserServiceInterface::class);
    }

    public function testCreateUserSuccessfully(): void
    {
        $dto = CreateUserDataDTO::fromArray([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'role' => 'user'
        ]);
        
        $user = $this->userService->createUser($dto);

        $this->assertInstanceOf(UserEntityInterface::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john.doe@example.com', $user->email);
        $this->assertEquals('user', $user->role);
        $this->assertEquals('active', $user->status);
        $this->assertNotNull($user->getId());
        $this->assertNotNull($user->uuid);
        $this->assertNotNull($user->createdAt);
        $this->assertNotNull($user->updatedAt);
    }

    public function testCreateUserWithInvalidEmail(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Formato de email inválido: Formato de email inválido');

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
        // Create first user
        $firstDto = CreateUserDataDTO::fromArray([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'role' => 'user'
        ]);
        $this->userService->createUser($firstDto);

        // Try to create second user with same email
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Email \'john.doe@example.com\' já está em uso');

        $secondDto = CreateUserDataDTO::fromArray([
            'name' => 'Jane Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password456',
            'role' => 'user'
        ]);
        $this->userService->createUser($secondDto);
    }

    public function testCreateUserWithShortPassword(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Senha deve ter pelo menos 6 caracteres');

        $dto = CreateUserDataDTO::fromArray([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => '123',
            'role' => 'user'
        ]);

        $this->userService->createUser($dto);
    }

    public function testCreateUserWithInvalidRole(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Função inválida. Deve ser uma das: admin, user, moderator');

        $dto = CreateUserDataDTO::fromArray([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'role' => 'invalid_role'
        ]);

        $this->userService->createUser($dto);
    }

    public function testGetUserById(): void
    {
        $dto = CreateUserDataDTO::fromArray([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'role' => 'user'
        ]);
        $createdUser = $this->userService->createUser($dto);

        $foundUser = $this->userService->processUserById($createdUser->getId(), fn($user) => $user);

        $this->assertInstanceOf(UserEntityInterface::class, $foundUser);
        $this->assertEquals($createdUser->getId(), $foundUser->getId());
        $this->assertEquals('John Doe', $foundUser->name);
        $this->assertEquals('john.doe@example.com', $foundUser->email);
    }

    public function testGetUserByEmail(): void
    {
        $dto = CreateUserDataDTO::fromArray([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'role' => 'user'
        ]);
        $createdUser = $this->userService->createUser($dto);

        // Buscar usuário por email usando authenticateUserByEmail sem senha (apenas para teste)
        $foundUser = null;
        try {
            $foundUser = $this->userService->processAllUsers(function($user) {
                return $user->email === 'john.doe@example.com' ? $user : null;
            });
            $foundUser = array_filter($foundUser)[0] ?? null;
        } catch (\Exception $e) {
            $foundUser = null;
        }

        $this->assertInstanceOf(UserEntityInterface::class, $foundUser);
        $this->assertEquals($createdUser->getId(), $foundUser->getId());
        $this->assertEquals('John Doe', $foundUser->name);
    }

    public function testUpdateUser(): void
    {
        $dto = CreateUserDataDTO::fromArray([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'role' => 'user'
        ]);
        
        $user = $this->userService->createUser($dto);

        $updateDto = UpdateUserDataDTO::fromArray([
            'name' => 'John Updated',
            'role' => 'admin'
        ]);
        
        $updatedUser = $this->userService->updateUser($user->getId(), $updateDto);

        $this->assertEquals('John Updated', $updatedUser->name);
        $this->assertEquals('admin', $updatedUser->role);
        $this->assertEquals('john.doe@example.com', $updatedUser->email); // Should remain unchanged
    }

    public function testUpdateNonExistentUser(): void
    {
        $this->expectException(BusinessLogicExceptionAbstract::class);
        $this->expectExceptionMessage('Usuário com ID 99999 não encontrado');

        $updateDto = UpdateUserDataDTO::fromArray(['name' => 'Updated Name']);
        $this->userService->updateUser(99999, $updateDto);
    }

    public function testDeleteUser(): void
    {
        $dto = CreateUserDataDTO::fromArray([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'role' => 'user'
        ]);
        
        $user = $this->userService->createUser($dto);

        $userId = $user->getId();
        $result = $this->userService->deleteUser($userId);

        $this->assertTrue($result);
        // Verificar se usuário foi deletado
        try {
            $this->userService->processUserById($userId, fn($user) => $user);
            $this->fail('Usuário deveria ter sido deletado');
        } catch (\App\Domain\Common\Exceptions\Impl\BusinessLogicExceptionAbstract $e) {
            $this->assertTrue(true); // Usuário foi deletado corretamente
        }
    }

    public function testDeleteNonExistentUser(): void
    {
        $result = $this->userService->deleteUser(99999);
        $this->assertFalse($result);
    }

    public function testAuthenticateUser(): void
    {
        $dto = CreateUserDataDTO::fromArray([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'role' => 'user'
        ]);
        
        $user = $this->userService->createUser($dto);

        $authenticatedUser = $this->userService->authenticateUser('john.doe@example.com', 'password123');

        $this->assertInstanceOf(UserEntityInterface::class, $authenticatedUser);
        $this->assertEquals($user->getId(), $authenticatedUser->getId());
    }

    public function testAuthenticateUserWithWrongPassword(): void
    {
        $dto = CreateUserDataDTO::fromArray([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'role' => 'user'
        ]);
        
        $this->userService->createUser($dto);

        $authenticatedUser = $this->userService->authenticateUser('john.doe@example.com', 'wrongpassword');

        $this->assertNull($authenticatedUser);
    }

    public function testActivateAndDeactivateUser(): void
    {
        $dto = CreateUserDataDTO::fromArray([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'role' => 'user'
        ]);
        
        $user = $this->userService->createUser($dto);

        // Deactivate user
        $deactivatedUser = $this->userService->deactivateUser($user->getId());
        $this->assertEquals('inactive', $deactivatedUser->status);

        // Activate user
        $activatedUser = $this->userService->activateUser($user->getId());
        $this->assertEquals('active', $activatedUser->status);
    }

    public function testChangePassword(): void
    {
        $dto = CreateUserDataDTO::fromArray([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'role' => 'user'
        ]);
        
        $user = $this->userService->createUser($dto);

        // Password não é mais exposto - usando método authenticate() para validar
        $this->userService->changePassword($user->getId(), 'newpassword456');

        // Verify new password works (this will fetch fresh data)
        $authenticatedUser = $this->userService->authenticateUser('john.doe@example.com', 'newpassword456');
        $this->assertInstanceOf(UserEntityInterface::class, $authenticatedUser);
        
        // Verify old password doesn't work
        $oldAuth = $this->userService->authenticateUser('john.doe@example.com', 'password123');
        $this->assertNull($oldAuth);
    }

    public function testGetAllUsers(): void
    {
        // Create multiple users
        $this->userService->createUser(CreateUserDataDTO::fromArray(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => 'password123', 'role' => 'user']));
        $this->userService->createUser(CreateUserDataDTO::fromArray(['name' => 'User 2', 'email' => 'user2@example.com', 'password' => 'password123', 'role' => 'admin']));
        $this->userService->createUser(CreateUserDataDTO::fromArray(['name' => 'User 3', 'email' => 'user3@example.com', 'password' => 'password123', 'role' => 'user']));

        $users = $this->userService->processAllUsers(fn($user) => $user);

        $this->assertCount(3, $users);
        $this->assertContainsOnlyInstancesOf(UserEntityInterface::class, $users);
    }

    public function testGetUsersByRole(): void
    {
        // Create users with different roles
        $this->userService->createUser(CreateUserDataDTO::fromArray(['name' => 'Admin 1', 'email' => 'admin1@example.com', 'password' => 'password123', 'role' => 'admin']));
        $this->userService->createUser(CreateUserDataDTO::fromArray(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => 'password123', 'role' => 'user']));
        $this->userService->createUser(CreateUserDataDTO::fromArray(['name' => 'Admin 2', 'email' => 'admin2@example.com', 'password' => 'password123', 'role' => 'admin']));

        $adminUsers = $this->userService->processUsersByRole('admin', fn($user) => $user);
        $regularUsers = $this->userService->processUsersByRole('user', fn($user) => $user);

        $this->assertCount(2, $adminUsers);
        $this->assertCount(1, $regularUsers);
    }

    public function testSearchUsersByName(): void
    {
        $this->userService->createUser(CreateUserDataDTO::fromArray(['name' => 'John Smith', 'email' => 'john.smith@example.com', 'password' => 'password123', 'role' => 'user']));
        $this->userService->createUser(CreateUserDataDTO::fromArray(['name' => 'Jane Smith', 'email' => 'jane.smith@example.com', 'password' => 'password123', 'role' => 'user']));
        $this->userService->createUser(CreateUserDataDTO::fromArray(['name' => 'Bob Johnson', 'email' => 'bob.johnson@example.com', 'password' => 'password123', 'role' => 'user']));

        $smithUsers = $this->userService->searchUsersByName('Smith');
        $johnUsers = $this->userService->searchUsersByName('Johnson');

        $this->assertCount(2, $smithUsers);
        $this->assertCount(1, $johnUsers);
    }

    public function testGetUserCount(): void
    {
        $initialStats = $this->userService->generateUserStatistics();
        $initialCount = $initialStats['total'];

        $this->userService->createUser(CreateUserDataDTO::fromArray(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => 'password123', 'role' => 'user']));
        $this->userService->createUser(CreateUserDataDTO::fromArray(['name' => 'User 2', 'email' => 'user2@example.com', 'password' => 'password123', 'role' => 'admin']));

        $newStats = $this->userService->generateUserStatistics();
        $newCount = $newStats['total'];

        $this->assertEquals($initialCount + 2, $newCount);
    }

    public function testIsEmailAvailable(): void
    {
        $dto = CreateUserDataDTO::fromArray(['name' => 'John Doe', 'email' => 'john@example.com', 'password' => 'password123', 'role' => 'user']);
        $user = $this->userService->createUser($dto);

        $this->assertFalse($this->userService->validateEmailAvailability('john@example.com'));
        $this->assertTrue($this->userService->validateEmailAvailability('jane@example.com'));
        $this->assertTrue($this->userService->validateEmailAvailability('john@example.com', $user->getId())); // Exclude current user
    }
}
