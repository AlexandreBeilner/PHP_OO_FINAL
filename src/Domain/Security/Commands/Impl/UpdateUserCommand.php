<?php

declare(strict_types=1);

namespace App\Domain\Security\Commands\Impl;

use App\Domain\Security\Services\UserServiceInterface;
use App\Domain\Security\Entities\UserEntityInterface;
use App\Domain\Security\DTOs\Impl\UpdateUserDataDTO;

/**
 * Command puro para atualização de usuário
 * 
 * SRP: Responsabilidade única de executar ação de atualização
 * Usa DTO puro interno para dados
 */
final class UpdateUserCommand
{
    private UpdateUserDataDTO $data;
    
    public function __construct(UpdateUserDataDTO $data)
    {
        $this->data = $data;
    }

    /**
     * Executa comando de atualização (Tell, Don't Ask)
     */
    public function executeWithUserId(UserServiceInterface $userService, int $userId): ?UserEntityInterface
    {
        return $userService->updateUser($userId, $this->data);
    }

    /**
     * Factory method para criação a partir de array
     */
    public static function fromArray(array $data): self
    {
        return new self(UpdateUserDataDTO::fromArray($data));
    }
}