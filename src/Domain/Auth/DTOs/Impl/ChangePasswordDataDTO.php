<?php

declare(strict_types=1);

namespace App\Domain\Auth\DTOs\Impl;

/**
 * DTO puro para dados de alteração de senha
 *
 * SRP: Responsabilidade única de transportar dados de senha
 * PHP 7.4: Sintaxe compatível sem readonly properties
 */
final class ChangePasswordDataDTO
{
    public string $currentPassword;
    public string $newPassword;
    public int $userId;

    public function __construct(
        int $userId,
        string $currentPassword,
        string $newPassword
    ) {
        $this->userId = $userId;
        $this->currentPassword = $currentPassword;
        $this->newPassword = $newPassword;
    }

    /**
     * Factory method para criação a partir de array (PHP 7.4 compatível)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (int) ($data['user_id'] ?? $data['userId'] ?? 0),
            $data['current_password'] ?? $data['currentPassword'] ?? '',
            $data['new_password'] ?? $data['newPassword'] ?? ''
        );
    }
}
