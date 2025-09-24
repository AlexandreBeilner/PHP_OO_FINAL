<?php

declare(strict_types=1);

namespace App\Domain\Security\Validators\Impl;

use App\Application\Shared\DTOs\Impl\ValidationResult;
use App\Domain\Security\Validators\UserDataValidatorInterface;

final class UserDataValidator implements UserDataValidatorInterface
{
    public function validateCreateUserData(array $data): ValidationResult
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

        if (empty($data['email'])) {
            $errors['email'] = 'Email é obrigatório';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email deve ter formato válido';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Senha é obrigatória';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Senha deve ter pelo menos 6 caracteres';
        }

        if (!empty($data['role'])) {
            $validRoles = ['admin', 'user', 'moderator'];
            if (!in_array($data['role'], $validRoles, true)) {
                $errors['role'] = 'Função inválida. Deve ser uma das: ' . implode(', ', $validRoles);
            }
        }

        return new ValidationResult(empty($errors), $errors);
    }

    public function validateUpdateUserData(array $data): ValidationResult
    {
        $errors = [];

        if (empty($data)) {
            $errors['dados'] = 'Dados são obrigatórios';
            return new ValidationResult(false, $errors);
        }

        if (!empty($data['name']) && strlen(trim($data['name'])) < 2) {
            $errors['name'] = 'Nome deve ter pelo menos 2 caracteres';
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email deve ter formato válido';
        }

        if (!empty($data['password']) && strlen($data['password']) < 6) {
            $errors['password'] = 'Senha deve ter pelo menos 6 caracteres';
        }

        if (!empty($data['role'])) {
            $validRoles = ['admin', 'user', 'moderator'];
            if (!in_array($data['role'], $validRoles, true)) {
                $errors['role'] = 'Função inválida. Deve ser uma das: ' . implode(', ', $validRoles);
            }
        }

        return new ValidationResult(empty($errors), $errors);
    }

    public function validateUserId(int $id): ValidationResult
    {
        $errors = [];

        if ($id <= 0) {
            $errors['id'] = 'ID do usuário é obrigatório e deve ser um número positivo';
        }

        return new ValidationResult(empty($errors), $errors);
    }
}
