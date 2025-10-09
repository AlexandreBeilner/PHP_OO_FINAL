<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Security\Commands\Impl;

use App\Domain\Security\Commands\Impl\CreateUserCommand;
use App\Domain\Security\DTOs\Impl\CreateUserDataDTO;
use App\Domain\Security\Entities\UserEntityInterface;
use App\Domain\Security\Services\UserServiceInterface;
use PHPUnit\Framework\TestCase;

final class CreateUserCommandTest extends TestCase
{
    public function testCommandIsImmutable(): void
    {
        $data = new CreateUserDataDTO('Original', 'original@test.com', 'pass123');
        $command = new CreateUserCommand($data);

        // O comando não deve ter métodos para modificar os dados
        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->exactly(2))
            ->method('createUser')
            ->with($this->callback(function (CreateUserDataDTO $dto) {
                return $dto->name === 'Original';
            }))
            ->willReturn($this->createMock(UserEntityInterface::class));

        // Executar duas vezes deve usar os mesmos dados
        $command->executeWith($mockUserService);
        $command->executeWith($mockUserService);
    }

    public function testConstructorSetsData(): void
    {
        $data = new CreateUserDataDTO('John Doe', 'john@example.com', 'password123', 'admin');
        $command = new CreateUserCommand($data);

        // Verificamos indiretamente através do comportamento do executeWith
        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->once())
            ->method('createUser')
            ->with($this->identicalTo($data))
            ->willReturn($this->createMock(UserEntityInterface::class));

        $command->executeWith($mockUserService);
    }

    public function testExecuteWithAlwaysReturnsUserEntity(): void
    {
        $data = new CreateUserDataDTO('Test User', 'test@example.com', 'testpass');
        $command = new CreateUserCommand($data);
        $mockUser = $this->createMock(UserEntityInterface::class);

        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->once())
            ->method('createUser')
            ->with($data)
            ->willReturn($mockUser);

        $result = $command->executeWith($mockUserService);

        $this->assertInstanceOf(UserEntityInterface::class, $result);
    }

    public function testExecuteWithCallsUserServiceCreateUser(): void
    {
        $data = new CreateUserDataDTO('Jane Smith', 'jane@test.com', 'secret456', 'user');
        $command = new CreateUserCommand($data);
        $mockUser = $this->createMock(UserEntityInterface::class);

        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->once())
            ->method('createUser')
            ->with($data)
            ->willReturn($mockUser);

        $result = $command->executeWith($mockUserService);

        $this->assertSame($mockUser, $result);
    }

    public function testFromArrayCreatesCommandWithCreateUserDataDTO(): void
    {
        $data = [
            'name' => 'Bob Wilson',
            'email' => 'bob@company.com',
            'password' => 'bobpass123',
            'role' => 'moderator',
        ];

        $command = CreateUserCommand::fromArray($data);

        // Verificamos indiretamente através do comportamento
        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->once())
            ->method('createUser')
            ->with($this->callback(function (CreateUserDataDTO $dto) use ($data) {
                return $dto->name === $data['name']
                    && $dto->email === $data['email']
                    && $dto->password === $data['password']
                    && $dto->role === $data['role'];
            }))
            ->willReturn($this->createMock(UserEntityInterface::class));

        $command->executeWith($mockUserService);
    }

    public function testFromArrayCreatesNewInstance(): void
    {
        $data1 = ['name' => 'User1', 'email' => 'user1@test.com', 'password' => 'pass1'];
        $data2 = ['name' => 'User2', 'email' => 'user2@test.com', 'password' => 'pass2'];

        $command1 = CreateUserCommand::fromArray($data1);
        $command2 = CreateUserCommand::fromArray($data2);

        $this->assertNotSame($command1, $command2);

        // Verificar que são independentes
        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->exactly(2))
            ->method('createUser')
            ->withConsecutive(
                [$this->callback(fn (CreateUserDataDTO $dto) => $dto->name === 'User1')],
                [$this->callback(fn (CreateUserDataDTO $dto) => $dto->name === 'User2')]
            )
            ->willReturn($this->createMock(UserEntityInterface::class));

        $command1->executeWith($mockUserService);
        $command2->executeWith($mockUserService);
    }

    public function testFromArrayWithEmptyArray(): void
    {
        $command = CreateUserCommand::fromArray([]);

        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->once())
            ->method('createUser')
            ->with($this->callback(function (CreateUserDataDTO $dto) {
                return $dto->name === ''
                    && $dto->email === ''
                    && $dto->password === ''
                    && $dto->role === 'user'; // Default role
            }))
            ->willReturn($this->createMock(UserEntityInterface::class));

        $command->executeWith($mockUserService);
    }

    public function testFromArrayWithEmptyValues(): void
    {
        $data = [
            'name' => '',
            'email' => '',
            'password' => '',
            'role' => '',
        ];

        $command = CreateUserCommand::fromArray($data);

        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->once())
            ->method('createUser')
            ->with($this->callback(function (CreateUserDataDTO $dto) {
                return $dto->name === ''
                    && $dto->email === ''
                    && $dto->password === ''
                    && $dto->role === '';
            }))
            ->willReturn($this->createMock(UserEntityInterface::class));

        $command->executeWith($mockUserService);
    }

    public function testFromArrayWithExtraFieldsIgnoresThem(): void
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

        $command = CreateUserCommand::fromArray($data);

        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->once())
            ->method('createUser')
            ->with($this->callback(function (CreateUserDataDTO $dto) use ($data) {
                // Verifica que apenas os campos esperados são mantidos
                return $dto->name === $data['name']
                    && $dto->email === $data['email']
                    && $dto->password === $data['password']
                    && $dto->role === $data['role'];
            }))
            ->willReturn($this->createMock(UserEntityInterface::class));

        $command->executeWith($mockUserService);
    }

    public function testFromArrayWithMissingRoleUsesDefault(): void
    {
        $data = [
            'name' => 'Alice Brown',
            'email' => 'alice@test.com',
            'password' => 'alicepass',
        ];

        $command = CreateUserCommand::fromArray($data);

        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->expects($this->once())
            ->method('createUser')
            ->with($this->callback(function (CreateUserDataDTO $dto) use ($data) {
                return $dto->name === $data['name']
                    && $dto->email === $data['email']
                    && $dto->password === $data['password']
                    && $dto->role === 'user'; // Default role
            }))
            ->willReturn($this->createMock(UserEntityInterface::class));

        $command->executeWith($mockUserService);
    }
}
