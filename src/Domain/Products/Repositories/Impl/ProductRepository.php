<?php

declare(strict_types=1);

namespace App\Domain\Products\Repositories\Impl;

use App\Domain\Common\Repositories\Impl\AbstractRepository;
use App\Domain\Products\Entities\Impl\ProductEntity;
use App\Domain\Products\Repositories\ProductRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class ProductRepository extends AbstractRepository implements ProductRepositoryInterface
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
    }

    public function findActiveProducts(): array
    {
        return $this->findBy(['status' => 'active']);
    }

    public function findByCategory(string $category): array
    {
        return $this->findBy(['category' => $category]);
    }

    public function findByPriceRange(float $minPrice, float $maxPrice): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.price >= :minPrice')
            ->andWhere('p.price <= :maxPrice')
            ->setParameter('minPrice', $minPrice)
            ->setParameter('maxPrice', $maxPrice);
        return $qb->getQuery()->getResult();
    }

    public function searchByName(string $name): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.name LIKE :name')
            ->setParameter('name', '%' . $name . '%');
        return $qb->getQuery()->getResult();
    }

    protected function getEntityClass(): string
    {
        return ProductEntity::class;
    }
}
