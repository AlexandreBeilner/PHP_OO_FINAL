<?php

declare(strict_types=1);

namespace App\Domain\Security\Repositories;

use App\Domain\Common\Repositories\AbstractRepositoryInterface;
use App\Domain\Security\Entities\UserEntityInterface;

interface UserRepositoryInterface extends AbstractRepositoryInterface
{
    public function findByEmail(string $email): ?UserEntityInterface;

    public function findByRole(string $role): array;

    public function searchByName(string $name): array;
}
