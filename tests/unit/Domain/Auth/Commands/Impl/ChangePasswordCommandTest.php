<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Auth\Commands\Impl;

use App\Domain\Auth\Commands\Impl\ChangePasswordCommand;
use App\Domain\Auth\DTOs\Impl\ChangePasswordDataDTO;
use App\Domain\Security\Entities\UserEntityInterface;
use App\Domain\Security\Services\AuthServiceInterface;
use PHPUnit\Framework\TestCase;

final class ChangePasswordCommandTest extends TestCase
{
    public function testCommandIsImmutable(): void
    {
        $data = new ChangePasswordDataDTO(8, 'original', 'newpass');
        $command = new ChangePasswordCommand($data);

        // O comando não deve ter métodos para modificar os dados
        $mockAuthService = $this->createMock(AuthServiceInterface::class);
        $mockAuthService->expects($this->exactly(2))
            ->method('changePassword')
            ->with($this->callback(function (ChangePasswordDataDTO $dto) {
                return $dto->userId === 8;
            }))
            ->willReturn(null);

        // Executar duas vezes deve usar os mesmos dados
        $command->executeWith($mockAuthService);
        $command->executeWith($mockAuthService);
    }

    public function testConstructorSetsData(): void
    {
        $data = new ChangePasswordDataDTO(1, 'oldpass', 'newpass123');
        $command = new ChangePasswordCommand($data);

        // Verificamos indiretamente através do comportamento do executeWith
        $mockAuthService = $this->createMock(AuthServiceInterface::class);
        $mockAuthService->expects($this->once())
            ->method('changePassword')
            ->with($this->identicalTo($data))
            ->willReturn(null);

        $command->executeWith($mockAuthService);
    }

    public function testExecuteWithCallsAuthServiceChangePassword(): void
    {
        $data = new ChangePasswordDataDTO(5, 'current123', 'newsecret456');
        $command = new ChangePasswordCommand($data);
        $mockUser = $this->createMock(UserEntityInterface::class);

        $mockAuthService = $this->createMock(AuthServiceInterface::class);
        $mockAuthService->expects($this->once())
            ->method('changePassword')
            ->with($data)
            ->willReturn($mockUser);

        $result = $command->executeWith($mockAuthService);

        $this->assertSame($mockUser, $result);
    }

    public function testExecuteWithPreservesUserEntityType(): void
    {
        $data = new ChangePasswordDataDTO(2, 'old', 'new');
        $command = new ChangePasswordCommand($data);
        $mockUser = $this->createMock(UserEntityInterface::class);

        $mockAuthService = $this->createMock(AuthServiceInterface::class);
        $mockAuthService->method('changePassword')->willReturn($mockUser);

        $result = $command->executeWith($mockAuthService);

        $this->assertInstanceOf(UserEntityInterface::class, $result);
    }

    public function testExecuteWithReturnsNullWhenChangePasswordFails(): void
    {
        $data = new ChangePasswordDataDTO(10, 'wrongcurrent', 'newpass');
        $command = new ChangePasswordCommand($data);

        $mockAuthService = $this->createMock(AuthServiceInterface::class);
        $mockAuthService->expects($this->once())
            ->method('changePassword')
            ->with($data)
            ->willReturn(null);

        $result = $command->executeWith($mockAuthService);

        $this->assertNull($result);
    }

    public function testFromArrayCreatesCommandWithChangePasswordDataDTO(): void
    {
        $data = [
            'userId' => 7,
            'currentPassword' => 'oldpass123',
            'newPassword' => 'newpass456',
        ];

        $command = ChangePasswordCommand::fromArray($data);

        // Verificamos indiretamente através do comportamento
        $mockAuthService = $this->createMock(AuthServiceInterface::class);
        $mockAuthService->expects($this->once())
            ->method('changePassword')
            ->with($this->callback(function (ChangePasswordDataDTO $dto) use ($data) {
                return $dto->userId === $data['userId']
                    && $dto->currentPassword === $data['currentPassword']
                    && $dto->newPassword === $data['newPassword'];
            }))
            ->willReturn(null);

        $command->executeWith($mockAuthService);
    }

    public function testFromArrayCreatesNewInstance(): void
    {
        $data1 = ['userId' => 1, 'currentPassword' => 'old1', 'newPassword' => 'new1'];
        $data2 = ['userId' => 2, 'currentPassword' => 'old2', 'newPassword' => 'new2'];

        $command1 = ChangePasswordCommand::fromArray($data1);
        $command2 = ChangePasswordCommand::fromArray($data2);

        $this->assertNotSame($command1, $command2);

        // Verificar que são independentes
        $mockAuthService = $this->createMock(AuthServiceInterface::class);
        $mockAuthService->expects($this->exactly(2))
            ->method('changePassword')
            ->withConsecutive(
                [$this->callback(fn (ChangePasswordDataDTO $dto) => $dto->userId === 1)],
                [$this->callback(fn (ChangePasswordDataDTO $dto) => $dto->userId === 2)]
            )
            ->willReturn(null);

        $command1->executeWith($mockAuthService);
        $command2->executeWith($mockAuthService);
    }

    public function testFromArrayWithEmptyArray(): void
    {
        $command = ChangePasswordCommand::fromArray([]);

        $mockAuthService = $this->createMock(AuthServiceInterface::class);
        $mockAuthService->expects($this->once())
            ->method('changePassword')
            ->with($this->callback(function (ChangePasswordDataDTO $dto) {
                return $dto->userId === 0
                    && $dto->currentPassword === ''
                    && $dto->newPassword === '';
            }))
            ->willReturn(null);

        $command->executeWith($mockAuthService);
    }

    public function testFromArrayWithMissingData(): void
    {
        $data = ['userId' => 3, 'currentPassword' => 'current'];
        $command = ChangePasswordCommand::fromArray($data);

        $mockAuthService = $this->createMock(AuthServiceInterface::class);
        $mockAuthService->expects($this->once())
            ->method('changePassword')
            ->with($this->callback(function (ChangePasswordDataDTO $dto) {
                return $dto->userId === 3
                    && $dto->currentPassword === 'current'
                    && $dto->newPassword === '';
            }))
            ->willReturn(null);

        $command->executeWith($mockAuthService);
    }
}
