<?php

declare(strict_types=1);

namespace Tests\Integration\Domain\Security\Services;

use Tests\Integration\AbstractBaseIntegrationTest;
use App\Domain\Security\Services\UserServiceInterface;
use App\Domain\Security\Entities\UserEntityInterface;
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
        $user = $this->userService->createUser(
            'John Doe',
            'john.doe@example.com',
            'password123',
            'user'
        );

        $this->assertInstanceOf(UserEntityInterface::class, $user);
        $this->assertEquals('John Doe', $user->getName());
        $this->assertEquals('john.doe@example.com', $user->getEmail());
        $this->assertEquals('user', $user->getRole());
        $this->assertEquals('active', $user->getStatus());
        $this->assertNotNull($user->getId());
        $this->assertNotNull($user->getUuid());
        $this->assertNotNull($user->getCreatedAt());
        $this->assertNotNull($user->getUpdatedAt());
    }

    public function testCreateUserWithInvalidEmail(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Formato de email inválido: Formato de email inválido');

        $this->userService->createUser(
            'John Doe',
            'invalid-email',
            'password123',
            'user'
        );
    }

    public function testCreateUserWithDuplicateEmail(): void
    {
        // Create first user
        $this->userService->createUser(
            'John Doe',
            'john.doe@example.com',
            'password123',
            'user'
        );

        // Try to create second user with same email
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Email \'john.doe@example.com\' já está em uso');

        $this->userService->createUser(
            'Jane Doe',
            'john.doe@example.com',
            'password456',
            'user'
        );
    }

    public function testCreateUserWithShortPassword(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Senha deve ter pelo menos 6 caracteres');

        $this->userService->createUser(
            'John Doe',
            'john.doe@example.com',
            '123',
            'user'
        );
    }

    public function testCreateUserWithInvalidRole(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Função inválida. Deve ser uma das: admin, user, moderator');

        $this->userService->createUser(
            'John Doe',
            'john.doe@example.com',
            'password123',
            'invalid_role'
        );
    }

    public function testGetUserById(): void
    {
        $createdUser = $this->userService->createUser(
            'John Doe',
            'john.doe@example.com',
            'password123',
            'user'
        );

        $foundUser = $this->userService->getUserById($createdUser->getId());

        $this->assertInstanceOf(UserEntityInterface::class, $foundUser);
        $this->assertEquals($createdUser->getId(), $foundUser->getId());
        $this->assertEquals('John Doe', $foundUser->getName());
        $this->assertEquals('john.doe@example.com', $foundUser->getEmail());
    }

    public function testGetUserByEmail(): void
    {
        $createdUser = $this->userService->createUser(
            'John Doe',
            'john.doe@example.com',
            'password123',
            'user'
        );

        $foundUser = $this->userService->getUserByEmail('john.doe@example.com');

        $this->assertInstanceOf(UserEntityInterface::class, $foundUser);
        $this->assertEquals($createdUser->getId(), $foundUser->getId());
        $this->assertEquals('John Doe', $foundUser->getName());
    }

    public function testUpdateUser(): void
    {
        $user = $this->userService->createUser(
            'John Doe',
            'john.doe@example.com',
            'password123',
            'user'
        );

        $updatedUser = $this->userService->updateUser($user->getId(), [
            'name' => 'John Updated',
            'role' => 'admin'
        ]);

        $this->assertEquals('John Updated', $updatedUser->getName());
        $this->assertEquals('admin', $updatedUser->getRole());
        $this->assertEquals('john.doe@example.com', $updatedUser->getEmail()); // Should remain unchanged
    }

    public function testUpdateNonExistentUser(): void
    {
        $this->expectException(BusinessLogicExceptionAbstract::class);
        $this->expectExceptionMessage('Usuário com ID 99999 não encontrado');

        $this->userService->updateUser(99999, ['name' => 'Updated Name']);
    }

    public function testDeleteUser(): void
    {
        $user = $this->userService->createUser(
            'John Doe',
            'john.doe@example.com',
            'password123',
            'user'
        );

        $userId = $user->getId();
        $result = $this->userService->deleteUser($userId);

        $this->assertTrue($result);
        $this->assertNull($this->userService->getUserById($userId));
    }

    public function testDeleteNonExistentUser(): void
    {
        $result = $this->userService->deleteUser(99999);
        $this->assertFalse($result);
    }

    public function testAuthenticateUser(): void
    {
        $user = $this->userService->createUser(
            'John Doe',
            'john.doe@example.com',
            'password123',
            'user'
        );

        $authenticatedUser = $this->userService->authenticateUser('john.doe@example.com', 'password123');

        $this->assertInstanceOf(UserEntityInterface::class, $authenticatedUser);
        $this->assertEquals($user->getId(), $authenticatedUser->getId());
    }

    public function testAuthenticateUserWithWrongPassword(): void
    {
        $this->userService->createUser(
            'John Doe',
            'john.doe@example.com',
            'password123',
            'user'
        );

        $authenticatedUser = $this->userService->authenticateUser('john.doe@example.com', 'wrongpassword');

        $this->assertNull($authenticatedUser);
    }

    public function testActivateAndDeactivateUser(): void
    {
        $user = $this->userService->createUser(
            'John Doe',
            'john.doe@example.com',
            'password123',
            'user'
        );

        // Deactivate user
        $deactivatedUser = $this->userService->deactivateUser($user->getId());
        $this->assertEquals('inactive', $deactivatedUser->getStatus());

        // Activate user
        $activatedUser = $this->userService->activateUser($user->getId());
        $this->assertEquals('active', $activatedUser->getStatus());
    }

    public function testChangePassword(): void
    {
        $user = $this->userService->createUser(
            'John Doe',
            'john.doe@example.com',
            'password123',
            'user'
        );

        $originalPassword = $user->getPassword();
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
        $this->userService->createUser('User 1', 'user1@example.com', 'password123', 'user');
        $this->userService->createUser('User 2', 'user2@example.com', 'password123', 'admin');
        $this->userService->createUser('User 3', 'user3@example.com', 'password123', 'user');

        $users = $this->userService->getAllUsers();

        $this->assertCount(3, $users);
        $this->assertContainsOnlyInstancesOf(UserEntityInterface::class, $users);
    }

    public function testGetUsersByRole(): void
    {
        // Create users with different roles
        $this->userService->createUser('Admin 1', 'admin1@example.com', 'password123', 'admin');
        $this->userService->createUser('User 1', 'user1@example.com', 'password123', 'user');
        $this->userService->createUser('Admin 2', 'admin2@example.com', 'password123', 'admin');

        $adminUsers = $this->userService->getUsersByRole('admin');
        $regularUsers = $this->userService->getUsersByRole('user');

        $this->assertCount(2, $adminUsers);
        $this->assertCount(1, $regularUsers);
    }

    public function testSearchUsersByName(): void
    {
        $this->userService->createUser('John Smith', 'john.smith@example.com', 'password123', 'user');
        $this->userService->createUser('Jane Smith', 'jane.smith@example.com', 'password123', 'user');
        $this->userService->createUser('Bob Johnson', 'bob.johnson@example.com', 'password123', 'user');

        $smithUsers = $this->userService->searchUsersByName('Smith');
        $johnUsers = $this->userService->searchUsersByName('Johnson');

        $this->assertCount(2, $smithUsers);
        $this->assertCount(1, $johnUsers);
    }

    public function testGetUserCount(): void
    {
        $initialCount = $this->userService->getUserCount();

        $this->userService->createUser('User 1', 'user1@example.com', 'password123', 'user');
        $this->userService->createUser('User 2', 'user2@example.com', 'password123', 'admin');

        $newCount = $this->userService->getUserCount();

        $this->assertEquals($initialCount + 2, $newCount);
    }

    public function testIsEmailAvailable(): void
    {
        $user = $this->userService->createUser('John Doe', 'john@example.com', 'password123', 'user');

        $this->assertFalse($this->userService->isEmailAvailable('john@example.com'));
        $this->assertTrue($this->userService->isEmailAvailable('jane@example.com'));
        $this->assertTrue($this->userService->isEmailAvailable('john@example.com', $user->getId())); // Exclude current user
    }
}
