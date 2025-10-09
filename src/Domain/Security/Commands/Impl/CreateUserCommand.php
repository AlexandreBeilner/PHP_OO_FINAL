<?php

declare(strict_types=1);

namespace App\Domain\Security\Commands\Impl;

use App\Domain\Security\DTOs\Impl\CreateUserDataDTO;
use App\Domain\Security\Entities\UserEntityInterface;
use App\Domain\Security\Services\UserServiceInterface;

/**
 * Command puro para criação de usuário
 *
 * SRP: Responsabilidade única de executar ação de criação
 * Usa DTO puro interno para dados
 */
final class CreateUserCommand
{
    private CreateUserDataDTO $data;

    public function __construct(CreateUserDataDTO $data)
    {
        $this->data = $data;
    }

    /**
     * Executa comando de criação (Tell, Don't Ask)
     */
    public function executeWith(UserServiceInterface $userService): UserEntityInterface
    {
        return $userService->createUser($this->data);
    }

    /**
     * Factory method para criação a partir de array
     */
    public static function fromArray(array $data): self
    {
        return new self(CreateUserDataDTO::fromArray($data));
    }
}
