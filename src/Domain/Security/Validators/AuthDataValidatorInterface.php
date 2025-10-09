<?php

declare(strict_types=1);

namespace App\Domain\Security\Validators;

use App\Application\Shared\DTOs\Impl\ValidationResult;

interface AuthDataValidatorInterface
{
    public function validateAuthData(array $data): ValidationResult;

    public function validateChangePasswordData(array $data): ValidationResult;

    public function validateLoginData(array $data): ValidationResult;

    public function validateUserId(int $id): ValidationResult;
}
