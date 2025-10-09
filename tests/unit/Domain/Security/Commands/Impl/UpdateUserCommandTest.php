<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Security\Commands\Impl;

use App\Domain\Security\Commands\Impl\UpdateUserCommand;
use App\Domain\Security\DTOs\Impl\UpdateUserDataDTO;
use App\Domain\Security\Entities\UserEntityInterface;
use App\Domain\Security\Services\UserServiceInterface;
use PHPUnit\Framework\TestCase;

final class UpdateUserCommandTest extends TestCase
{
    public function testCommandIsImmutable(): void
    {
        $data = new UpdateUserDataDTO('Original User', 'original@test.com');
        $command = new UpdateUserCommand($data);

        // O comando não deve ter métodos para modificar os dados
        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->exactly(2))
            ->method('updateUser')
            ->with($this->anything(), $this->callback(function (UpdateUserDataDTO $dto) {
                return $dto->name === 'Original User';
            }))
            ->willReturn($this->createMock(UserEntityInterface::class));

        // Executar duas vezes deve usar os mesmos dados
        $command->executeWithUserId($mockUserService, 1);
        $command->executeWithUserId($mockUserService, 2);
    }

    public function testConstructorSetsData(): void
    {
        $data = new UpdateUserDataDTO('Jane Doe', 'jane@example.com', null, 'admin', 'active');
        $command = new UpdateUserCommand($data);

        // Verificamos indiretamente através do comportamento do executeWithUserId
        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->once())
            ->method('updateUser')
            ->with(5, $this->identicalTo($data))
            ->willReturn($this->createMock(UserEntityInterface::class));

        $command->executeWithUserId($mockUserService, 5);
    }

    public function testExecuteWithUserIdCallsUserServiceUpdateUser(): void
    {
        $data = new UpdateUserDataDTO('Updated Name', null, 'newpass', 'moderator');
        $command = new UpdateUserCommand($data);
        $mockUser = $this->createMock(UserEntityInterface::class);
        $userId = 42;

        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->once())
            ->method('updateUser')
            ->with($userId, $data)
            ->willReturn($mockUser);

        $result = $command->executeWithUserId($mockUserService, $userId);

        $this->assertSame($mockUser, $result);
    }

    public function testExecuteWithUserIdPreservesUserEntityType(): void
    {
        $data = new UpdateUserDataDTO('Any User');
        $command = new UpdateUserCommand($data);
        $mockUser = $this->createMock(UserEntityInterface::class);

        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->method('updateUser')->willReturn($mockUser);

        $result = $command->executeWithUserId($mockUserService, 1);

        $this->assertInstanceOf(UserEntityInterface::class, $result);
    }

    public function testExecuteWithUserIdWithDifferentUserIds(): void
    {
        $data = new UpdateUserDataDTO('Test', 'test@example.com');
        $command = new UpdateUserCommand($data);
        $mockUser = $this->createMock(UserEntityInterface::class);

        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->exactly(2))
            ->method('updateUser')
            ->withConsecutive([100, $data], [200, $data])
            ->willReturn($mockUser);

        $result1 = $command->executeWithUserId($mockUserService, 100);
        $result2 = $command->executeWithUserId($mockUserService, 200);

        $this->assertSame($mockUser, $result1);
        $this->assertSame($mockUser, $result2);
    }

    public function testFromArrayCreatesCommandWithUpdateUserDataDTO(): void
    {
        $data = [
            'name' => 'Bob Smith',
            'email' => 'bob@company.com',
            'role' => 'admin',
            'status' => 'inactive',
        ];

        $command = UpdateUserCommand::fromArray($data);

        // Verificamos indiretamente através do comportamento
        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->once())
            ->method('updateUser')
            ->with(10, $this->callback(function (UpdateUserDataDTO $dto) use ($data) {
                return $dto->name === $data['name']
                    && $dto->email === $data['email']
                    && $dto->password === null
                    && $dto->role === $data['role']
                    && $dto->status === $data['status'];
            }))
            ->willReturn($this->createMock(UserEntityInterface::class));

        $command->executeWithUserId($mockUserService, 10);
    }

    public function testFromArrayCreatesNewInstance(): void
    {
        $data1 = ['name' => 'User1', 'email' => 'user1@test.com'];
        $data2 = ['name' => 'User2', 'email' => 'user2@test.com'];

        $command1 = UpdateUserCommand::fromArray($data1);
        $command2 = UpdateUserCommand::fromArray($data2);

        $this->assertNotSame($command1, $command2);

        // Verificar que são independentes
        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->exactly(2))
            ->method('updateUser')
            ->withConsecutive(
                [30, $this->callback(fn (UpdateUserDataDTO $dto) => $dto->name === 'User1')],
                [40, $this->callback(fn (UpdateUserDataDTO $dto) => $dto->name === 'User2')]
            )
            ->willReturn($this->createMock(UserEntityInterface::class));

        $command1->executeWithUserId($mockUserService, 30);
        $command2->executeWithUserId($mockUserService, 40);
    }

    public function testFromArrayWithEmptyArray(): void
    {
        $command = UpdateUserCommand::fromArray([]);

        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->once())
            ->method('updateUser')
            ->with(20, $this->callback(function (UpdateUserDataDTO $dto) {
                return $dto->name === null
                    && $dto->email === null
                    && $dto->password === null
                    && $dto->role === null
                    && $dto->status === null;
            }))
            ->willReturn($this->createMock(UserEntityInterface::class));

        $command->executeWithUserId($mockUserService, 20);
    }

    public function testFromArrayWithNullValues(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => null,
            'password' => null,
            'role' => null,
            'status' => null,
        ];

        $command = UpdateUserCommand::fromArray($data);

        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->once())
            ->method('updateUser')
            ->with(15, $this->callback(function (UpdateUserDataDTO $dto) {
                return $dto->name === 'Test User'
                    && $dto->email === null
                    && $dto->password === null
                    && $dto->role === null
                    && $dto->status === null;
            }))
            ->willReturn($this->createMock(UserEntityInterface::class));

        $command->executeWithUserId($mockUserService, 15);
    }

    public function testFromArrayWithPartialUpdate(): void
    {
        $data = [
            'name' => 'Partial Update',
            'role' => 'user',
            // email, password, status são null (partial update)
        ];

        $command = UpdateUserCommand::fromArray($data);

        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->once())
            ->method('updateUser')
            ->with(25, $this->callback(function (UpdateUserDataDTO $dto) use ($data) {
                return $dto->name === $data['name']
                    && $dto->email === null // Não deve ser atualizado
                    && $dto->password === null // Não deve ser atualizado
                    && $dto->role === $data['role']
                    && $dto->status === null; // Não deve ser atualizado
            }))
            ->willReturn($this->createMock(UserEntityInterface::class));

        $command->executeWithUserId($mockUserService, 25);
    }
}
