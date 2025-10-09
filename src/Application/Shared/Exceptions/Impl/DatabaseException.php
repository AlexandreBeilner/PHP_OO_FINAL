<?php

declare(strict_types=1);

namespace App\Application\Shared\Exceptions\Impl;

use App\Application\Shared\Exceptions\DatabaseExceptionInterface;
use App\Domain\Common\Exceptions\Impl\AbstractBaseException;

final class DatabaseException extends AbstractBaseException implements DatabaseExceptionInterface
{
    private array $parameters = [];
    private ?string $query = null;

    public function __construct(string $message = "Database error", ?string $query = null, array $parameters = [], int $code = 500)
    {
        parent::__construct($message, $code);
        $this->query = $query;
        $this->parameters = $parameters;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }
}
