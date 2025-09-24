<?php

declare(strict_types=1);

namespace App\Domain\Auth\Commands\Impl;

use App\Domain\Security\Services\AuthServiceInterface;
use App\Domain\Security\Entities\UserEntityInterface;
use App\Domain\Auth\DTOs\Impl\ChangePasswordDataDTO;

/**
 * Command puro para alteração de senha
 * 
 * SRP: Responsabilidade única de executar ação de alteração de senha
 * Usa DTO puro interno para dados
 */
final class ChangePasswordCommand
{
    private ChangePasswordDataDTO $data;
    
    public function __construct(ChangePasswordDataDTO $data)
    {
        $this->data = $data;
    }

    /**
     * Executa comando de alteração de senha (Tell, Don't Ask)
     */
    public function executeWith(AuthServiceInterface $authService): ?UserEntityInterface
    {
        return $authService->changePassword($this->data);
    }

    /**
     * Factory method para criação a partir de array
     */
    public static function fromArray(array $data): self
    {
        return new self(ChangePasswordDataDTO::fromArray($data));
    }
}