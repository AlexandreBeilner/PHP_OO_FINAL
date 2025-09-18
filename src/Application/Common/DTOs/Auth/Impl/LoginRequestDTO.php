<?php

declare(strict_types=1);

namespace App\Application\Common\DTOs\Auth\Impl;

use App\Application\Common\DTOs\Auth\LoginRequestDTOInterface;

final class LoginRequestDTO implements LoginRequestDTOInterface
{
    private string $email;
    private string $password;

    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['email'],
            $data['password']
        );
    }
}
