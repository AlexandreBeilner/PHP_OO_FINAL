<?php

declare(strict_types=1);

namespace App\Domain\Security\Validators\Impl;

use App\Application\Common\DTOs\Impl\ValidationResult;
use App\Domain\Security\Validators\AuthDataValidatorInterface;

final class AuthDataValidator implements AuthDataValidatorInterface
{
    public function validateLoginData(array $data): ValidationResult
    {
        $errors = [];

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

        return new ValidationResult(empty($errors), $errors);
    }

    public function validateChangePasswordData(array $data): ValidationResult
    {
        $errors = [];

        if (empty($data['user_id'])) {
            $errors['user_id'] = 'ID do usuário é obrigatório';
        } elseif (!is_numeric($data['user_id']) || (int)$data['user_id'] <= 0) {
            $errors['user_id'] = 'ID do usuário deve ser um número positivo';
        }

        if (empty($data['current_password'])) {
            $errors['current_password'] = 'Senha atual é obrigatória';
        }

        if (empty($data['new_password'])) {
            $errors['new_password'] = 'Nova senha é obrigatória';
        } elseif (strlen($data['new_password']) < 6) {
            $errors['new_password'] = 'Nova senha deve ter pelo menos 6 caracteres';
        }

        return new ValidationResult(empty($errors), $errors);
    }

    public function validateAuthData(array $data): ValidationResult
    {
        $errors = [];

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
