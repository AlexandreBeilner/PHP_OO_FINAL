<?php

declare(strict_types=1);

namespace App\Domain\Products\Validators;

use App\Application\Shared\DTOs\Impl\ValidationResult;

interface ProductDataValidatorInterface
{
    public function validateCreateProductData(array $data): ValidationResult;

    public function validateUpdateProductData(array $data): ValidationResult;

    public function validateProductId(int $id): ValidationResult;
}
