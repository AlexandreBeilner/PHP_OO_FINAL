<?php

declare(strict_types=1);

namespace App\Domain\Security\Validators;

use App\Application\Common\DTOs\Impl\ValidationResult;

interface UserDataValidatorInterface
{
    public function validateCreateUserData(array $data): ValidationResult;
    public function validateUpdateUserData(array $data): ValidationResult;
    public function validateUserId(int $id): ValidationResult;
}
