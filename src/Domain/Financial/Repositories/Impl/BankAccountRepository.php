<?php

declare(strict_types=1);

namespace App\Domain\Financial\Repositories\Impl;

use App\Domain\Common\Repositories\Impl\AbstractRepository;
use App\Domain\Financial\Entities\Impl\BankAccountEntity;

final class BankAccountRepository extends AbstractRepository
{

    protected function getEntityClass(): string
    {
        return BankAccountEntity::class;
    }
}