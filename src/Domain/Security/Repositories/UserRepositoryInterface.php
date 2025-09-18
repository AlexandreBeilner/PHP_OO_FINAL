<?php

declare(strict_types=1);

namespace App\Domain\Security\Repositories;

use App\Domain\Common\Repositories\AbstractRepositoryInterface;
use App\Domain\Security\Entities\UserEntityInterface;

interface UserRepositoryInterface extends AbstractRepositoryInterface
{
    public function countUsersWithFilters(array $filters = []): int;

    public function findActiveUsers(): array;

    public function findByEmail(string $email): ?UserEntityInterface;

    public function findByEmailAndPassword(string $email, string $password): ?UserEntityInterface;

    public function findByRole(string $role): array;

    public function findByStatus(string $status): array;

    public function findInactiveUsers(): array;

    public function findUsersWithFilters(array $filters = [], ?array $orderBy = null, int $limit = 10, int $offset = 0): array;

    public function getUserStatistics(): array;

    public function searchByName(string $name): array;
}
