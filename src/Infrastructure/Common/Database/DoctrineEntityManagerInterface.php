<?php

declare(strict_types=1);

namespace App\Infrastructure\Common\Database;

use Doctrine\ORM\EntityManagerInterface;

interface DoctrineEntityManagerInterface
{
    public function getConnectionStats(): array;

    public function getEntityManager(string $operation = 'read'): EntityManagerInterface;

    public function getEntityManagerForQuery(string $queryType): EntityManagerInterface;

    public function getMaster(): EntityManagerInterface;

    public function getSlave(): EntityManagerInterface;

    public function testConnections(): array;
}
