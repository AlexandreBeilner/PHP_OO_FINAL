<?php

declare(strict_types=1);

namespace App\Domain\Common\Exceptions\Impl;

use App\Domain\Common\Exceptions\BaseExceptionInterface;
use Exception;

abstract class AbstractBaseException extends Exception implements BaseExceptionInterface
{
    private array $context = [];

    public function __construct(string $message = "", int $code = 0, ?Exception $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function addContext(string $key, $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
