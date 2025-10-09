<?php

declare(strict_types=1);

namespace App\Domain\Security\DTOs\Impl;

/**
 * DTO puro para dados de criação de usuário
 *
 * SRP: Responsabilidade única de transportar dados de criação
 * DRY: Herda propriedades comuns de AbstractBaseUserDataDTO
 */
final class CreateUserDataDTO extends AbstractBaseUserDataDTO
{
    public function __construct(
        string $name,
        string $email,
        string $password,
        string $role = 'user'
    ) {
        // Garantir que dados de criação são obrigatórios
        parent::__construct($name, $email, $password, $role);
    }

    /**
     * Factory method para criação a partir de array (PHP 7.4 compatível)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'] ?? '',
            $data['email'] ?? '',
            $data['password'] ?? '',
            $data['role'] ?? 'user'
        );
    }
}
