<?php

declare(strict_types=1);

namespace App\Domain\Products\Validators\Impl;

use App\Application\Shared\DTOs\Impl\ValidationResult;
use App\Domain\Products\Validators\ProductDataValidatorInterface;

final class ProductDataValidator implements ProductDataValidatorInterface
{
    public function validateCreateProductData(array $data): ValidationResult
    {
        $errors = [];

        if (empty($data)) {
            $errors['dados'] = 'Dados são obrigatórios';
            return new ValidationResult(false, $errors);
        }

        if (empty($data['name'])) {
            $errors['name'] = 'Nome é obrigatório';
        } elseif (strlen(trim($data['name'])) < 2) {
            $errors['name'] = 'Nome deve ter pelo menos 2 caracteres';
        }

        if (!isset($data['price'])) {
            $errors['price'] = 'Preço é obrigatório';
        } elseif (!is_numeric($data['price']) || (float) $data['price'] <= 0) {
            $errors['price'] = 'Preço deve ser um número positivo';
        }

        if (empty($data['category'])) {
            $errors['category'] = 'Categoria é obrigatória';
        } elseif (strlen(trim($data['category'])) < 2) {
            $errors['category'] = 'Categoria deve ter pelo menos 2 caracteres';
        }

        return new ValidationResult(empty($errors), $errors);
    }

    public function validateUpdateProductData(array $data): ValidationResult
    {
        $errors = [];

        if (empty($data)) {
            $errors['dados'] = 'Dados são obrigatórios';
            return new ValidationResult(false, $errors);
        }

        if (!empty($data['name']) && strlen(trim($data['name'])) < 2) {
            $errors['name'] = 'Nome deve ter pelo menos 2 caracteres';
        }

        if (isset($data['price']) && (!is_numeric($data['price']) || (float) $data['price'] <= 0)) {
            $errors['price'] = 'Preço deve ser um número positivo';
        }

        if (!empty($data['category']) && strlen(trim($data['category'])) < 2) {
            $errors['category'] = 'Categoria deve ter pelo menos 2 caracteres';
        }

        if (!empty($data['status'])) {
            $validStatuses = ['draft', 'active', 'inactive'];
            if (!in_array($data['status'], $validStatuses, true)) {
                $errors['status'] = 'Status inválido. Deve ser um dos: ' . implode(', ', $validStatuses);
            }
        }

        return new ValidationResult(empty($errors), $errors);
    }

    public function validateProductId(int $id): ValidationResult
    {
        $errors = [];
        
        if ($id <= 0) {
            $errors['id'] = 'ID do produto deve ser um número positivo';
        }
        
        return new ValidationResult(empty($errors), $errors);
    }

    public function validate(array $data): ValidationResult
    {
        return $this->validateCreateProductData($data);
    }
}
