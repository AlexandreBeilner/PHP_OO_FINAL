<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Modules\Security\Factories\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Modules\Security\Factories\Impl\UserCommandExecutor;
use App\Application\Shared\Controllers\Crud\CommandExecutorInterface;
use App\Domain\Security\Services\UserServiceInterface;

final class UserCommandExecutorTest extends TestCase
{
    private UserCommandExecutor $executor;
    private UserServiceInterface $userService;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserServiceInterface::class);
        $this->executor = new UserCommandExecutor($this->userService);
    }

    public function testImplementsExpectedInterface(): void
    {
        $this->assertInstanceOf(CommandExecutorInterface::class, $this->executor);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $userService = $this->createMock(UserServiceInterface::class);
        $instance = new UserCommandExecutor($userService);

        $this->assertInstanceOf(UserCommandExecutor::class, $instance);
        $this->assertInstanceOf(CommandExecutorInterface::class, $instance);
    }

    public function testConstructorWithDifferentServices(): void
    {
        $userService1 = $this->createMock(UserServiceInterface::class);
        $userService2 = $this->createMock(UserServiceInterface::class);

        $instance1 = new UserCommandExecutor($userService1);
        $instance2 = new UserCommandExecutor($userService2);

        $this->assertInstanceOf(UserCommandExecutor::class, $instance1);
        $this->assertInstanceOf(UserCommandExecutor::class, $instance2);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testDeleteByIdDelegatesToUserService(): void
    {
        $userId = 123;

        $this->userService
            ->expects($this->once())
            ->method('deleteUser')
            ->with($userId)
            ->willReturn(true);

        $result = $this->executor->deleteById($userId);

        $this->assertTrue($result);
    }

    public function testDeleteByIdReturnsFalseOnFailure(): void
    {
        $userId = 456;

        $this->userService
            ->expects($this->once())
            ->method('deleteUser')
            ->with($userId)
            ->willReturn(false);

        $result = $this->executor->deleteById($userId);

        $this->assertFalse($result);
    }

    public function testDeleteByIdWithDifferentIds(): void
    {
        $testIds = [1, 42, 999];

        foreach ($testIds as $id) {
            $userService = $this->createMock(UserServiceInterface::class);
            $executor = new UserCommandExecutor($userService);
            
            $userService
                ->expects($this->once())
                ->method('deleteUser')
                ->with($id)
                ->willReturn(true);

            $result = $executor->deleteById($id);
            $this->assertTrue($result);
        }
    }

    public function testExecuteDelegatesToCommand(): void
    {
        $command = new class {
            public $executed = false;
            public $service = null;
            
            public function executeWith($service) {
                $this->executed = true;
                $this->service = $service;
                return 'command_result';
            }
        };

        $result = $this->executor->execute($command);

        $this->assertEquals('command_result', $result);
        $this->assertTrue($command->executed);
        $this->assertSame($this->userService, $command->service);
    }

    public function testExecuteWithIdDelegatesToCommand(): void
    {
        $userId = 789;
        $command = new class {
            public $executed = false;
            public $service = null;
            public $userId = null;
            
            public function executeWithUserId($service, $id) {
                $this->executed = true;
                $this->service = $service;
                $this->userId = $id;
                return 'command_with_id_result';
            }
        };

        $result = $this->executor->executeWithId($command, $userId);

        $this->assertEquals('command_with_id_result', $result);
        $this->assertTrue($command->executed);
        $this->assertSame($this->userService, $command->service);
        $this->assertEquals($userId, $command->userId);
    }

    public function testFindAllDelegatesToUserService(): void
    {
        $expectedUsers = [
            ['id' => 1, 'name' => 'User 1'],
            ['id' => 2, 'name' => 'User 2']
        ];

        $this->userService
            ->expects($this->once())
            ->method('processAllUsers')
            ->willReturnCallback(function ($callback) use ($expectedUsers) {
                return array_map($callback, $expectedUsers);
            });

        $result = $this->executor->findAll();

        $this->assertEquals($expectedUsers, $result);
    }

    public function testFindByIdDelegatesToUserService(): void
    {
        $userId = 42;
        $expectedUser = ['id' => 42, 'name' => 'Test User'];

        $this->userService
            ->expects($this->once())
            ->method('processUserById')
            ->with($userId, $this->isType('callable'))
            ->willReturnCallback(function ($id, $callback) use ($expectedUser) {
                return $callback($expectedUser);
            });

        $result = $this->executor->findById($userId);

        $this->assertEquals($expectedUser, $result);
    }

    public function testFindByIdWithDifferentIds(): void
    {
        $testData = [
            ['id' => 1, 'user' => ['id' => 1, 'name' => 'User 1']],
            ['id' => 2, 'user' => ['id' => 2, 'name' => 'User 2']],
            ['id' => 3, 'user' => ['id' => 3, 'name' => 'User 3']]
        ];

        foreach ($testData as $test) {
            $userService = $this->createMock(UserServiceInterface::class);
            $executor = new UserCommandExecutor($userService);
            
            $userService
                ->expects($this->once())
                ->method('processUserById')
                ->with($test['id'], $this->isType('callable'))
                ->willReturnCallback(function ($id, $callback) use ($test) {
                    return $callback($test['user']);
                });

            $result = $executor->findById($test['id']);
            $this->assertEquals($test['user'], $result);
        }
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(UserCommandExecutor::class);
        
        $this->assertTrue($reflection->isFinal());
    }

    public function testDeleteByIdReturnsBooleanType(): void
    {
        $this->userService
            ->expects($this->once())
            ->method('deleteUser')
            ->with(123)
            ->willReturn(true);

        $result = $this->executor->deleteById(123);

        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testExecutorWorksWithDifferentUserServiceImplementations(): void
    {
        // Test that executor accepts any UserServiceInterface implementation
        $userService1 = $this->createMock(UserServiceInterface::class);
        $userService2 = $this->createMock(UserServiceInterface::class);

        $userService1->expects($this->once())
            ->method('deleteUser')
            ->with(1)
            ->willReturn(true);

        $userService2->expects($this->once())
            ->method('processUserById')
            ->willReturnCallback(function ($id, $callback) {
                return $callback(['id' => $id, 'name' => 'Test User']);
            });

        $executor1 = new UserCommandExecutor($userService1);
        $executor2 = new UserCommandExecutor($userService2);

        $result1 = $executor1->deleteById(1);
        $result2 = $executor2->findById(2);

        $this->assertTrue($result1);
        $this->assertEquals(['id' => 2, 'name' => 'Test User'], $result2);
    }

    public function testMultipleOperationsWork(): void
    {
        // Setup for findById
        $this->userService
            ->method('processUserById')
            ->willReturnCallback(function ($id, $callback) {
                return $callback(['id' => $id, 'name' => "User {$id}"]);
            });

        // Setup for deleteById
        $this->userService
            ->method('deleteUser')
            ->willReturn(true);

        // Test multiple operations
        $user = $this->executor->findById(1);
        $deleted = $this->executor->deleteById(1);

        $this->assertEquals(['id' => 1, 'name' => 'User 1'], $user);
        $this->assertTrue($deleted);
    }
}
