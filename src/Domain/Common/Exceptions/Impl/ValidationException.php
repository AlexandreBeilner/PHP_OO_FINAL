<?php

declare(strict_types=1);

namespace App\Domain\Common\Exceptions\Impl;

use App\Domain\Common\Exceptions\ValidationExceptionInterface;

final class ValidationException extends AbstractBaseException implements ValidationExceptionInterface
{
    private array $errors = [];

    public function __construct(string $message = "Validation failed", array $errors = [], int $code = 422)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public function addError(string $field, string $message): self
    {
        $this->errors[$field][] = $message;
        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }
}
