<?php

declare(strict_types=1);

namespace App\Domain\Auth\DTOs\Impl;

/**
 * DTO puro para dados de login
 * 
 * SRP: Responsabilidade única de transportar credenciais
 * PHP 7.4: Sintaxe compatível sem readonly properties
 */
final class LoginDataDTO
{
    public string $email;
    public string $password;

    public function __construct(
        string $email,
        string $password
    ) {
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Factory method para criação a partir de array (PHP 7.4 compatível)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['email'] ?? '',
            $data['password'] ?? ''
        );
    }
}
