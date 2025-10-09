<?php

declare(strict_types=1);

namespace App\Domain\Security\Services;

use App\Domain\Security\DTOs\Impl\CreateUserDataDTO;
use App\Domain\Security\DTOs\Impl\UpdateUserDataDTO;
use App\Domain\Security\Entities\UserEntityInterface;

interface UserServiceInterface
{
    public function activateUser(int $id): UserEntityInterface;

    public function authenticateUser(string $email, string $password): ?UserEntityInterface;

    public function authenticateUserByEmail(string $email, string $password): ?UserEntityInterface;

    public function changePassword(int $id, string $newPassword): UserEntityInterface;

    /**
     * Cria usuário usando DTO puro (SRP + Tell Don't Ask)
     */
    public function createUser(CreateUserDataDTO $data): UserEntityInterface;

    public function deactivateUser(int $id): UserEntityInterface;

    public function deleteUser(int $id): bool;

    public function generateUserStatistics(): array;

    public function processAllUsers(callable $action): array;

    public function processUserById(int $id, callable $action);

    public function processUsersByRole(string $role, callable $action): array;

    public function saveUser(UserEntityInterface $user): UserEntityInterface;

    public function searchUsersByName(string $name): array;

    /**
     * Atualiza usuário usando DTO puro (SRP + Tell Don't Ask)
     */
    public function updateUser(int $id, UpdateUserDataDTO $data): UserEntityInterface;

    public function validateEmailAvailability(string $email, ?int $excludeId = null): bool;

    public function validateSystemCapacity(int $maxUsers = 1000): bool;
}
