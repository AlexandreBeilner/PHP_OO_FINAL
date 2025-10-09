<?php

declare(strict_types=1);

namespace App\Application\Shared\DTOs\Impl;


final class ValidationResult
{
    private array $errors;
    private bool $isValid;

    public function __construct(bool $isValid, array $errors = [])
    {
        $this->isValid = $isValid;
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        return ! empty($this->errors) ? reset($this->errors) : null;
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }
}
