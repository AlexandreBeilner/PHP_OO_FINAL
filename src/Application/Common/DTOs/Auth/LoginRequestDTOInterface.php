<?php

declare(strict_types=1);

namespace App\Application\Common\DTOs\Auth;

interface LoginRequestDTOInterface
{
    public function getEmail(): ?string;
    public function getPassword(): ?string;
}
