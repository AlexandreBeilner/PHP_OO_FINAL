<?php

declare(strict_types=1);

namespace App\Domain\Common\Services\Impl;

use App\Domain\Common\Exceptions\Impl\ValidationException;
use App\Domain\Common\Repositories\AbstractRepositoryInterface;
use App\Domain\Common\Services\AbstractServiceInterface;
use App\Domain\Common\Validators\ValidatorInterface;

abstract class AbstractService implements AbstractServiceInterface
{
    protected AbstractRepositoryInterface $repository;
    protected ValidatorInterface $validator;

    public function __construct(
        AbstractRepositoryInterface $repository,
        ValidatorInterface $validator
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
    }

    public function count(array $criteria = []): int
    {
        return $this->repository->count($criteria);
    }

    public function delete(object $entity): bool
    {
        return $this->repository->delete($entity);
    }

    public function exists(int $id): bool
    {
        return $this->repository->count(['id' => $id]) > 0;
    }

    public function save(object $entity): object
    {
        $this->validateEntity($entity);
        return $this->repository->save($entity);
    }

    abstract protected function extractEntityData(object $entity): array;

    protected function validateEntity(object $entity): void
    {
        $entityData = $this->extractEntityData($entity);

        if (! $this->validator->validate($entityData)) {
            throw new ValidationException(
                'Validation failed: ' . $this->validator->getErrorMessage(),
                $entityData
            );
        }
    }
}
