<?php

declare(strict_types=1);

namespace App\Application\Common\DTOs\User\Impl;

use App\Application\Common\DTOs\User\CreateUserRequestDTOInterface;

final class CreateUserRequestDTO implements CreateUserRequestDTOInterface
{
    private string $name;
    private string $email;
    private string $password;
    private string $role;

    public function __construct(string $name, string $email, string $password, string $role = 'user')
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRole(): string
    {
        return $this->role;
    }

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
