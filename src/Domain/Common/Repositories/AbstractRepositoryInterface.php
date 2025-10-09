<?php

declare(strict_types=1);

namespace App\Domain\Common\Repositories;

interface AbstractRepositoryInterface
{
    public function count(array $criteria = []): int;

    public function delete(object $entity): bool;

    public function deleteById(int $id): bool;

    public function executeDql(string $dql, array $parameters = []): int;

    public function exists(array $criteria): bool;

    public function find($id, $lockMode = null, $lockVersion = null): ?object;

    public function findByDql(string $dql, array $parameters = []): array;

    public function findPaginated(array $criteria = [], ?array $orderBy = null, int $limit = 10, int $offset = 0): array;

    public function save(object $entity): object;
}
