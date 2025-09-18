<?php

declare(strict_types=1);

namespace App\Application\Common\DTOs\Auth\Impl;

use App\Application\Common\DTOs\Auth\ChangePasswordRequestDTOInterface;

final class ChangePasswordRequestDTO implements ChangePasswordRequestDTOInterface
{
    private int $userId;
    private string $currentPassword;
    private string $newPassword;

    public function __construct(int $userId, string $currentPassword, string $newPassword)
    {
        $this->userId = $userId;
        $this->currentPassword = $currentPassword;
        $this->newPassword = $newPassword;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getCurrentPassword(): string
    {
        return $this->currentPassword;
    }

    public function getNewPassword(): string
    {
        return $this->newPassword;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['user_id'],
            $data['current_password'],
            $data['new_password']
        );
    }
}
