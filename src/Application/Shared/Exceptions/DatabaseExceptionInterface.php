<?php

declare(strict_types=1);

namespace App\Application\Shared\Exceptions;

use App\Domain\Common\Exceptions\BaseExceptionInterface;

interface DatabaseExceptionInterface extends BaseExceptionInterface
{
    public function getParameters(): array;

    public function getQuery(): ?string;
}
