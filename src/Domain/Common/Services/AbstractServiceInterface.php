<?php

declare(strict_types=1);

namespace App\Domain\Common\Services;

interface AbstractServiceInterface
{
    public function count(array $criteria = []): int;

    public function delete(object $entity): bool;

    public function exists(int $id): bool;

    public function save(object $entity): object;
}
