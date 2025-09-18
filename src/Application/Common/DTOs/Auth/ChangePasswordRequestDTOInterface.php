<?php

declare(strict_types=1);

namespace App\Application\Common\DTOs\Auth;

interface ChangePasswordRequestDTOInterface
{
    public function getUserId(): int;
    public function getCurrentPassword(): string;
    public function getNewPassword(): string;
}
