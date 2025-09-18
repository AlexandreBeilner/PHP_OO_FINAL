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

    /**
     * Conta usuários com filtros
     */
    public function countUsersWithFilters(array $filters = []): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)');

        // Aplicar os mesmos filtros da busca
        if (isset($filters['name']) && ! empty($filters['name'])) {
            $qb->andWhere('u.name LIKE :name')
                ->setParameter('name', '%' . $filters['name'] . '%');
        }

        if (isset($filters['email']) && ! empty($filters['email'])) {
            $qb->andWhere('u.email LIKE :email')
                ->setParameter('email', '%' . $filters['email'] . '%');
        }

        if (isset($filters['role']) && ! empty($filters['role'])) {
            $qb->andWhere('u.role = :role')
                ->setParameter('role', $filters['role']);
        }

        if (isset($filters['status']) && ! empty($filters['status'])) {
            $qb->andWhere('u.status = :status')
                ->setParameter('status', $filters['status']);
        }

        if (isset($filters['created_from']) && ! empty($filters['created_from'])) {
            $qb->andWhere('u.createdAt >= :created_from')
                ->setParameter('created_from', $filters['created_from']);
        }

        if (isset($filters['created_to']) && ! empty($filters['created_to'])) {
            $qb->andWhere('u.createdAt <= :created_to')
                ->setParameter('created_to', $filters['created_to']);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findActiveUsers(): array
    {
        return $this->findBy(['status' => 'active']);
    }

    public function findByEmail(string $email): ?UserEntityInterface
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findByEmailAndPassword(string $email, string $password): ?UserEntityInterface
    {
        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user->getPassword())) {
            return $user;
        }

        return null;
    }

    public function findByRole(string $role): array
    {
        return $this->findBy(['role' => $role]);
    }

    public function findByStatus(string $status): array
    {
        return $this->findBy(['status' => $status]);
    }

    public function findInactiveUsers(): array
    {
        return $this->findBy(['status' => 'inactive']);
    }

    /**
     * Busca usuários com paginação e filtros avançados
     */
    public function findUsersWithFilters(array $filters = [], ?array $orderBy = null, int $limit = 10, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('u');

        // Aplicar filtros
        if (isset($filters['name']) && ! empty($filters['name'])) {
            $qb->andWhere('u.name LIKE :name')
                ->setParameter('name', '%' . $filters['name'] . '%');
        }

        if (isset($filters['email']) && ! empty($filters['email'])) {
            $qb->andWhere('u.email LIKE :email')
                ->setParameter('email', '%' . $filters['email'] . '%');
        }

        if (isset($filters['role']) && ! empty($filters['role'])) {
            $qb->andWhere('u.role = :role')
                ->setParameter('role', $filters['role']);
        }

        if (isset($filters['status']) && ! empty($filters['status'])) {
            $qb->andWhere('u.status = :status')
                ->setParameter('status', $filters['status']);
        }

        if (isset($filters['created_from']) && ! empty($filters['created_from'])) {
            $qb->andWhere('u.createdAt >= :created_from')
                ->setParameter('created_from', $filters['created_from']);
        }

        if (isset($filters['created_to']) && ! empty($filters['created_to'])) {
            $qb->andWhere('u.createdAt <= :created_to')
                ->setParameter('created_to', $filters['created_to']);
        }

        // Aplicar ordenação
        if ($orderBy) {
            foreach ($orderBy as $field => $direction) {
                $qb->addOrderBy("u.{$field}", $direction);
            }
        } else {
            $qb->orderBy('u.createdAt', 'DESC');
        }

        // Aplicar paginação
        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Busca estatísticas de usuários
     */
    public function getUserStatistics(): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select([
                'COUNT(u.id) as total_users',
                'COUNT(CASE WHEN u.status = \'active\' THEN 1 END) as active_users',
                'COUNT(CASE WHEN u.status = \'inactive\' THEN 1 END) as inactive_users',
                'COUNT(CASE WHEN u.role = \'admin\' THEN 1 END) as admin_users',
                'COUNT(CASE WHEN u.role = \'user\' THEN 1 END) as regular_users',
                'COUNT(CASE WHEN u.role = \'moderator\' THEN 1 END) as moderator_users',
            ]);

        return $qb->getQuery()->getSingleResult();
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