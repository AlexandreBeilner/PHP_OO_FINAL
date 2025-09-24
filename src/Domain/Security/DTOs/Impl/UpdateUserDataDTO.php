<?php

declare(strict_types=1);

namespace App\Domain\Security\DTOs\Impl;

/**
 * DTO puro para dados de atualização de usuário
 * 
 * SRP: Responsabilidade única de transportar dados de atualização
 * DRY: Herda propriedades comuns de AbstractBaseUserDataDTO + status próprio
 */
final class UpdateUserDataDTO extends AbstractBaseUserDataDTO
{
    public ?string $status;
    
    public function __construct(
        ?string $name = null,
        ?string $email = null,
        ?string $password = null,
        ?string $role = null,
        ?string $status = null
    ) {
        parent::__construct($name, $email, $password, $role);
        $this->status = $status;
    }

    /**
     * Factory method para criação a partir de array (PHP 7.4 compatível)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'] ?? null,
            $data['email'] ?? null,
            $data['password'] ?? null,
            $data['role'] ?? null,
            $data['status'] ?? null
        );
    }

    /**
     * Converte para array (apenas campos não-nulos)
     */
    public function toArray(): array
    {
        $data = $this->baseToArray();
        
        if ($this->status !== null) {
            $data['status'] = $this->status;
        }

        return $data;
    }
}
