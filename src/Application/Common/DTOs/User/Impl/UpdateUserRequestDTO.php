<?php

declare(strict_types=1);

namespace App\Application\Common\DTOs\User\Impl;

use App\Application\Common\DTOs\User\UpdateUserRequestDTOInterface;

final class UpdateUserRequestDTO implements UpdateUserRequestDTOInterface
{
    private ?string $name;
    private ?string $email;
    private ?string $password;
    private ?string $role;
    private ?string $status;

    public function __construct(?string $name = null, ?string $email = null, ?string $password = null, ?string $role = null, ?string $status = null)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->status = $status;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

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

    public function toArray(): array
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
        if ($this->status !== null) {
            $data['status'] = $this->status;
        }

        return $data;
    }
}
