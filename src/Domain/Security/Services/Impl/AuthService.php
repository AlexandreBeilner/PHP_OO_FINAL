<?php

declare(strict_types=1);

namespace App\Domain\Security\Services\Impl;

use App\Domain\Common\Exceptions\Impl\ValidationException;
use App\Domain\Security\Services\AuthServiceInterface;
use App\Domain\Security\Services\UserServiceInterface;
use Exception;

final class AuthService implements AuthServiceInterface
{
    private UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function authenticate(string $email, string $password): array
    {
        try {
            // Busca usuário por email
            $user = $this->userService->getUserByEmail($email);

            if (! $user) {
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado',
                    'user' => null,
                ];
            }

            // Verifica senha (implementação básica - em produção usar password_hash)
            if ($user->getPassword() !== $password) {
                return [
                    'success' => false,
                    'message' => 'Senha incorreta',
                    'user' => null,
                ];
            }

            // Gera token
            $token = $this->generateToken([
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
            ]);

            return [
                'success' => true,
                'message' => 'Autenticação realizada com sucesso',
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $user->getName(),
                    'created_at' => $user->getCreatedAt(),
                ],
                'token' => $token,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro na autenticação: ' . $e->getMessage(),
                'user' => null,
            ];
        }
    }

    public function extractIdFromUrl(string $url): ?int
    {
        // Extrai ID da URL (ex: /api/users/123 -> 123)
        if (preg_match('/\/(\d+)(?:\/|$)/', $url, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    public function generateToken(array $userData): string
    {
        // Implementação básica de token (em produção usar JWT)
        $payload = base64_encode(json_encode([
            'user_id' => $userData['id'],
            'email' => $userData['email'],
            'name' => $userData['name'],
            'timestamp' => time(),
        ]));

        return 'auth_' . $payload;
    }

    public function validateAuthData(array $data): array
    {
        $errors = [];

        // Validação de email
        if (empty($data['email'])) {
            $errors['email'] = 'Email é obrigatório';
        } elseif (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email deve ter formato válido';
        }

        // Validação de senha
        if (empty($data['password'])) {
            $errors['password'] = 'Senha é obrigatória';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Senha deve ter pelo menos 6 caracteres';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    public function validateToken(string $token): array
    {
        try {
            if (! str_starts_with($token, 'auth_')) {
                return [
                    'valid' => false,
                    'message' => 'Token inválido',
                    'user' => null,
                ];
            }

            $payload = substr($token, 5); // Remove 'auth_' prefix
            $data = json_decode(base64_decode($payload), true);

            if (! $data || ! isset($data['user_id'])) {
                return [
                    'valid' => false,
                    'message' => 'Token corrompido',
                    'user' => null,
                ];
            }

            // Verifica se token não expirou (24 horas)
            $tokenAge = time() - $data['timestamp'];
            if ($tokenAge > 86400) {
                return [
                    'valid' => false,
                    'message' => 'Token expirado',
                    'user' => null,
                ];
            }

            return [
                'valid' => true,
                'message' => 'Token válido',
                'user' => [
                    'id' => $data['user_id'],
                    'email' => $data['email'],
                    'name' => $data['name'],
                ],
            ];
        } catch (Exception $e) {
            return [
                'valid' => false,
                'message' => 'Erro na validação do token: ' . $e->getMessage(),
                'user' => null,
            ];
        }
    }
}
