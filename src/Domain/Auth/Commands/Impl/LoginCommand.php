<?php

declare(strict_types=1);

namespace App\Domain\Auth\Commands\Impl;

use App\Domain\Auth\DTOs\Impl\LoginDataDTO;
use App\Domain\Security\Entities\UserEntityInterface;
use App\Domain\Security\Services\AuthServiceInterface;

/**
 * Command puro para autenticação
 *
 * SRP: Responsabilidade única de executar ação de login
 * Usa DTO puro interno para credenciais
 */
final class LoginCommand
{
    private LoginDataDTO $credentials;

    public function __construct(LoginDataDTO $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * Executa comando de autenticação (Tell, Don't Ask)
     */
    public function executeWith(AuthServiceInterface $authService): ?UserEntityInterface
    {
        return $authService->authenticate($this->credentials);
    }

    /**
     * Factory method para criação a partir de array
     */
    public static function fromArray(array $data): self
    {
        return new self(LoginDataDTO::fromArray($data));
    }
}
