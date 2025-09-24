<?php

declare(strict_types=1);

namespace App\Domain\Security\Repositories\Impl;

use App\Domain\Common\Repositories\Impl\AbstractRepository;
use App\Domain\Security\Entities\Impl\UserEntity;
use App\Domain\Security\Entities\UserEntityInterface;
use App\Domain\Security\Repositories\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
    }

    public function findByEmail(string $email): ?UserEntityInterface
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findByRole(string $role): array
    {
        return $this->findBy(['role' => $role]);
    }

    public function searchByName(string $name): array
    {
        $qb = $this->createQueryBuilder('u');
        $qb->where('u.name LIKE :name')
            ->setParameter('name', '%' . $name . '%');
        return $qb->getQuery()->getResult();
    }

    protected function getEntityClass(): string
    {
        return \App\Domain\Security\Entities\Impl\UserEntity::class;
    }
}