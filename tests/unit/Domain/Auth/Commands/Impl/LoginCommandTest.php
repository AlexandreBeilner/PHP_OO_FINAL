<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Auth\Commands\Impl;

use App\Domain\Auth\Commands\Impl\LoginCommand;
use App\Domain\Auth\DTOs\Impl\LoginDataDTO;
use App\Domain\Security\Entities\UserEntityInterface;
use App\Domain\Security\Services\AuthServiceInterface;
use PHPUnit\Framework\TestCase;

final class LoginCommandTest extends TestCase
{
    public function testCommandIsImmutable(): void
    {
        $credentials = new LoginDataDTO('original@test.com', 'originalpass');
        $command = new LoginCommand($credentials);

        // O comando não deve ter métodos para modificar as credenciais
        // Isso é verificado pela ausência de setters e modificação das credenciais
        $mockAuthService = $this->createMock(AuthServiceInterface::class);
        $mockAuthService->expects($this->exactly(2))
            ->method('authenticate')
            ->with($this->callback(function (LoginDataDTO $dto) {
                return $dto->email === 'original@test.com';
            }))
            ->willReturn(null);

        // Executar duas vezes deve usar as mesmas credenciais
        $command->executeWith($mockAuthService);
        $command->executeWith($mockAuthService);
    }

    public function testConstructorSetsCredentials(): void
    {
        $credentials = new LoginDataDTO('test@example.com', 'password123');
        $command = new LoginCommand($credentials);

        // Verificamos indiretamente através do comportamento do executeWith
        $mockAuthService = $this->createMock(AuthServiceInterface::class);
        $mockAuthService->expects($this->once())
            ->method('authenticate')
            ->with($this->identicalTo($credentials))
            ->willReturn(null);

        $command->executeWith($mockAuthService);
    }

    public function testExecuteWithCallsAuthServiceAuthenticate(): void
    {
        $credentials = new LoginDataDTO('user@test.com', 'secret123');
        $command = new LoginCommand($credentials);
        $mockUser = $this->createMock(UserEntityInterface::class);

        $mockAuthService = $this->createMock(AuthServiceInterface::class);
        $mockAuthService->expects($this->once())
            ->method('authenticate')
            ->with($credentials)
            ->willReturn($mockUser);

        $result = $command->executeWith($mockAuthService);

        $this->assertSame($mockUser, $result);
    }

    public function testExecuteWithPreservesUserEntityType(): void
    {
        $credentials = new LoginDataDTO('test@example.com', 'password');
        $command = new LoginCommand($credentials);
        $mockUser = $this->createMock(UserEntityInterface::class);

        $mockAuthService = $this->createMock(AuthServiceInterface::class);
        $mockAuthService->method('authenticate')->willReturn($mockUser);

        $result = $command->executeWith($mockAuthService);

        $this->assertInstanceOf(UserEntityInterface::class, $result);
    }

    public function testExecuteWithReturnsNullWhenAuthenticationFails(): void
    {
        $credentials = new LoginDataDTO('invalid@test.com', 'wrongpassword');
        $command = new LoginCommand($credentials);

        $mockAuthService = $this->createMock(AuthServiceInterface::class);
        $mockAuthService->expects($this->once())
            ->method('authenticate')
            ->with($credentials)
            ->willReturn(null);

        $result = $command->executeWith($mockAuthService);

        $this->assertNull($result);
    }

    public function testFromArrayCreatesCommandWithLoginDataDTO(): void
    {
        $data = [
            'email' => 'admin@example.com',
            'password' => 'adminpass',
        ];

        $command = LoginCommand::fromArray($data);

        // Verificamos indiretamente através do comportamento
        $mockAuthService = $this->createMock(AuthServiceInterface::class);
        $mockAuthService->expects($this->once())
            ->method('authenticate')
            ->with($this->callback(function (LoginDataDTO $dto) use ($data) {
                return $dto->email === $data['email'] && $dto->password === $data['password'];
            }))
            ->willReturn(null);

        $command->executeWith($mockAuthService);
    }

    public function testFromArrayCreatesNewInstance(): void
    {
        $data1 = ['email' => 'user1@test.com', 'password' => 'pass1'];
        $data2 = ['email' => 'user2@test.com', 'password' => 'pass2'];

        $command1 = LoginCommand::fromArray($data1);
        $command2 = LoginCommand::fromArray($data2);

        $this->assertNotSame($command1, $command2);

        // Verificar que são independentes
        $mockAuthService = $this->createMock(AuthServiceInterface::class);
        $mockAuthService->expects($this->exactly(2))
            ->method('authenticate')
            ->withConsecutive(
                [$this->callback(fn (LoginDataDTO $dto) => $dto->email === 'user1@test.com')],
                [$this->callback(fn (LoginDataDTO $dto) => $dto->email === 'user2@test.com')]
            )
            ->willReturn(null);

        $command1->executeWith($mockAuthService);
        $command2->executeWith($mockAuthService);
    }

    public function testFromArrayWithEmptyArray(): void
    {
        $command = LoginCommand::fromArray([]);

        $mockAuthService = $this->createMock(AuthServiceInterface::class);
        $mockAuthService->expects($this->once())
            ->method('authenticate')
            ->with($this->callback(function (LoginDataDTO $dto) {
                return $dto->email === '' && $dto->password === '';
            }))
            ->willReturn(null);

        $command->executeWith($mockAuthService);
    }

    public function testFromArrayWithMissingData(): void
    {
        $data = ['email' => 'test@example.com'];
        $command = LoginCommand::fromArray($data);

        $mockAuthService = $this->createMock(AuthServiceInterface::class);
        $mockAuthService->expects($this->once())
            ->method('authenticate')
            ->with($this->callback(function (LoginDataDTO $dto) {
                return $dto->email === 'test@example.com' && $dto->password === '';
            }))
            ->willReturn(null);

        $command->executeWith($mockAuthService);
    }
}
