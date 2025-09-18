<?php

declare(strict_types=1);

namespace App\Domain\Common\Validators\Impl;

use App\Domain\Common\Validators\EmailValidatorInterface;

final class EmailValidator implements EmailValidatorInterface
{
    private string $errorMessage = 'Formato de email inválido';

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function isValidFormat(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function normalize(string $email): string
    {
        return strtolower(trim($email));
    }

    public function validate($value): bool
    {
        if (! is_string($value)) {
            $this->errorMessage = 'Email deve ser uma string';
            return false;
        }

        if (empty($value)) {
            $this->errorMessage = 'Email não pode estar vazio';
            return false;
        }

        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errorMessage = 'Formato de email inválido';
            return false;
        }

        return true;
    }
}
