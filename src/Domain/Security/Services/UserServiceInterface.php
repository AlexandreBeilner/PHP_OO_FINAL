<?php

declare(strict_types=1);

namespace App\Domain\Security\Services;

use App\Domain\Security\Entities\UserEntityInterface;

interface UserServiceInterface
{
    public function activateUser(int $id): UserEntityInterface;

    public function authenticateUser(string $email, string $password): ?UserEntityInterface;

    public function changePassword(int $id, string $newPassword): UserEntityInterface;

    public function createUser(string $name, string $email, string $password, string $role = 'user'): UserEntityInterface;

    public function deactivateUser(int $id): UserEntityInterface;

    public function deleteUser(int $id): bool;

    public function getActiveUsers(): array;

    public function getAllUsers(): array;

    public function getInactiveUsers(): array;

    public function getUserByEmail(string $email): ?UserEntityInterface;

    public function getUserById(int $id): ?UserEntityInterface;

    public function getUserCount(): int;

    public function getUserCountByRole(string $role): int;

    public function getUsersByRole(string $role): array;

    public function isEmailAvailable(string $email, ?int $excludeId = null): bool;

    public function searchUsersByName(string $name): array;

    public function updateUser(int $id, array $data): UserEntityInterface;
}
