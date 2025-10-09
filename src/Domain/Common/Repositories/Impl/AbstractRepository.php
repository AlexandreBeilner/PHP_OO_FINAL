<?php

declare(strict_types=1);

namespace App\Domain\Common\Repositories\Impl;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Exception;

/**
 * AbstractRepository baseado no EntityRepository do Doctrine ORM
 * Fornece métodos comuns para operações CRUD usando Doctrine ORM
 */
abstract class AbstractRepository extends EntityRepository
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct($entityManager, $entityManager->getClassMetadata($this->getEntityClass()));
    }

    /**
     * Conta entidades por critérios
     */
    public function count(array $criteria = []): int
    {
        $qb = $this->createQueryBuilder('e');

        if (! empty($criteria)) {
            $this->addCriteriaToQueryBuilder($qb, $criteria);
        }

        return (int) $qb->select('COUNT(e.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Remove uma entidade
     */
    public function delete(object $entity): bool
    {
        try {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Remove entidade por ID
     */
    public function deleteById(int $id): bool
    {
        $entity = $this->find($id);
        if (! $entity) {
            return false;
        }

        return $this->delete($entity);
    }

    /**
     * Executa DQL personalizada
     */
    public function executeDql(string $dql, array $parameters = []): int
    {
        $query = $this->entityManager->createQuery($dql);

        foreach ($parameters as $key => $value) {
            $query->setParameter($key, $value);
        }

        return $query->execute();
    }

    /**
     * Verifica se existe entidade com os critérios fornecidos
     */
    public function exists(array $criteria): bool
    {
        return $this->count($criteria) > 0;
    }

    /**
     * Encontra uma entidade por ID (delega para o EntityRepository)
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?object
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    /**
     * Busca com DQL personalizada
     */
    public function findByDql(string $dql, array $parameters = []): array
    {
        $query = $this->entityManager->createQuery($dql);

        foreach ($parameters as $key => $value) {
            $query->setParameter($key, $value);
        }

        return $query->getResult();
    }

    /**
     * Busca paginada
     */
    public function findPaginated(array $criteria = [], ?array $orderBy = null, int $limit = 10, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('e');

        if (! empty($criteria)) {
            $this->addCriteriaToQueryBuilder($qb, $criteria);
        }

        if ($orderBy) {
            foreach ($orderBy as $field => $direction) {
                $qb->addOrderBy("e.{$field}", $direction);
            }
        }

        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Salva uma entidade (insert ou update)
     */
    public function save(object $entity): object
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }

    /**
     * Adiciona critérios ao QueryBuilder
     */
    protected function addCriteriaToQueryBuilder(QueryBuilder $qb, array $criteria): void
    {
        $parameterIndex = 0;

        foreach ($criteria as $field => $value) {
            $parameterName = 'param' . $parameterIndex++;

            if (is_array($value)) {
                $qb->andWhere("e.{$field} IN (:" . $parameterName . ")");
                $qb->setParameter($parameterName, $value);
            } else {
                $qb->andWhere("e.{$field} = :" . $parameterName);
                $qb->setParameter($parameterName, $value);
            }
        }
    }

    /**
     * Retorna a classe da entidade gerenciada por este repositório
     */
    abstract protected function getEntityClass(): string;
}
