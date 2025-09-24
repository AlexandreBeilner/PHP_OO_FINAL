<?php

declare(strict_types=1);

namespace App\Domain\Security\DTOs\Impl;

/**
 * DTO base abstrato para dados de usuário (elimina duplicidade)
 * 
 * SRP: Responsabilidade única de transportar dados básicos de usuário
 * DRY: Evita repetição de propriedades comuns
 * PSR-12: Classe abstrata com prefixo "Abstract"
 */
abstract class AbstractBaseUserDataDTO
{
    public ?string $name;
    public ?string $email;
    public ?string $password;
    public ?string $role;

    public function __construct(
        ?string $name = null,
        ?string $email = null,
        ?string $password = null,
        ?string $role = null
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    /**
     * Cria dados base a partir de array
     */
    protected static function extractBaseData(array $data): array
    {
        return [
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => $data['password'] ?? null,
            'role' => $data['role'] ?? null,
        ];
    }

    /**
     * Converte propriedades base para array (apenas campos não-nulos)
     */
    protected function baseToArray(): array
    {
        $data = [];
        
        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        if ($this->email !== null) {
            $data['email'] = $this->email;
        }
        if ($this->password !== null) {
            $data['password'] = $this->password;
        }
        if ($this->role !== null) {
            $data['role'] = $this->role;
        }

        return $data;
    }
}
