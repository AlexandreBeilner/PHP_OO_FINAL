<?php

declare(strict_types=1);

namespace App\Domain\Security\Services\Impl;

use App\Domain\Auth\DTOs\Impl\ChangePasswordDataDTO;
use App\Domain\Auth\DTOs\Impl\LoginDataDTO;
use App\Domain\Security\Entities\UserEntityInterface;
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

    /**
     * Autentica usando DTO puro (SRP + Tell Don't Ask)
     */
    public function authenticate(LoginDataDTO $credentials): ?UserEntityInterface
    {
        // Busca usuário por email usando propriedade readonly do DTO
        $user = $this->userService->getUserByEmail($credentials->email);
        if (! $user) {
            return null;
        }

        // Usa comportamento da entidade (Tell, Don't Ask)
        if ($user->authenticate($credentials->password)) {
            return $user;
        }

        return null;
    }

    /**
     * Autentica e valida permissões - Tell, Don't Ask
     */
    public function authenticateWithPermissions(string $email, string $password, string $requiredAction): array
    {
        $user = $this->userService->getUserByEmail($email);
        if (! $user) {
            return [
                'success' => false,
                'message' => 'Usuário não encontrado',
                'user' => null,
                'canPerform' => false,
            ];
        }

        if (! $user->authenticate($password)) {
            return [
                'success' => false,
                'message' => 'Credenciais inválidas',
                'user' => null,
                'canPerform' => false,
            ];
        }

        $canPerform = $user->canPerform($requiredAction);

        return [
            'success' => true,
            'message' => 'Autenticação realizada com sucesso',
            'user' => $user,
            'canPerform' => $canPerform,
            'needsPasswordChange' => $user->needsPasswordChange(),
        ];
    }

    public function authenticateWithResponse(string $email, string $password): array
    {
        try {
            $user = $this->userService->getUserByEmail($email);

            if (! $user) {
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado',
                    'user' => null,
                ];
            }

            if (! $user->authenticate($password)) {
                return [
                    'success' => false,
                    'message' => 'Senha incorreta',
                    'user' => null,
                ];
            }

            // Gera token
            $token = $this->generateToken([
                'id' => $user->getId(),
                'email' => $user->email ?? '',
                'name' => $user->name ?? '',
            ]);

            return [
                'success' => true,
                'message' => 'Autenticação realizada com sucesso',
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->email ?? '',
                    'name' => $user->name ?? '',
                    'created_at' => $user->createdAt,
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

    /**
     * Altera senha usando DTO puro (SRP + Tell Don't Ask)
     */
    public function changePassword(ChangePasswordDataDTO $data): ?UserEntityInterface
    {
        // Busca usuário usando processUserById (que existe na interface)
        $user = $this->userService->processUserById($data->userId, fn ($user) => $user);
        if (! $user) {
            return null;
        }

        // Valida senha atual usando comportamento da entidade
        if (! $user->authenticate($data->currentPassword)) {
            return null; // Current password is incorrect
        }

        // Atualiza senha usando comportamento da entidade (Tell, Don't Ask)
        $user->updatePassword($data->newPassword);

        return $this->userService->saveUser($user);
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

    /**
     * Força logout de usuários inativos - Tell, Don't Ask
     */
    public function logoutInactiveUsers(): array
    {
        // Em um sistema real, isso verificaria sessões ativas
        $inactiveUsers = $this->userService->deactivateInactiveUsers();

        return [
            'loggedOutUsers' => count($inactiveUsers),
            'users' => array_map(function ($user) {
                return [
                    'id' => $user->getId(),
                    'email' => $user->email ?? '',
                    'lastActivity' => $user->getUpdatedAt(),
                ];
            }, $inactiveUsers),
        ];
    }

    // ✅ TELL, DON'T ASK: High-level behavioral methods for Auth

    /**
     * @param array $data
     * @return array<>
     */
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
