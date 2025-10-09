<?php

declare(strict_types=1);

namespace App\Domain\Common\Exceptions;

interface ValidationExceptionInterface extends BaseExceptionInterface
{
    public function addError(string $field, string $message): self;

    public function getErrors(): array;

    public function hasErrors(): bool;
}
