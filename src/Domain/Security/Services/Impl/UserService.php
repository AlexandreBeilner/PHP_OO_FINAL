<?php

declare(strict_types=1);

namespace App\Domain\Security\Services\Impl;

use App\Domain\Common\Exceptions\Impl\BusinessLogicExceptionAbstract;
use App\Domain\Common\Exceptions\Impl\ValidationException;
use App\Domain\Common\Services\Impl\AbstractService;
use App\Domain\Common\Validators\EmailValidatorInterface;
use App\Domain\Security\DTOs\Impl\CreateUserDataDTO;
use App\Domain\Security\DTOs\Impl\UpdateUserDataDTO;
use App\Domain\Security\Entities\Impl\UserEntity;
use App\Domain\Security\Entities\UserEntityInterface;
use App\Domain\Security\Repositories\UserRepositoryInterface;
use App\Domain\Security\Services\UserServiceInterface;
use InvalidArgumentException;

final class UserService extends AbstractService implements UserServiceInterface
{
    private EmailValidatorInterface $emailValidator;

    public function __construct(
        UserRepositoryInterface $repository,
        EmailValidatorInterface $emailValidator
    ) {
        parent::__construct($repository, $emailValidator);
        $this->emailValidator = $emailValidator;
    }

    public function activateUser(int $id): UserEntityInterface
    {
        return $this->processUserById($id, function ($user) {
            $user->activate();
            return $this->repository->save($user);
        });
    }

    public function authenticateUser(string $email, string $password): ?UserEntityInterface
    {
        return $this->authenticateUserByEmail($email, $password);
    }

    /**
     * Autentica usuário por email e senha
     */
    public function authenticateUserByEmail(string $email, string $password): ?UserEntityInterface
    {
        $user = $this->repository->findByEmail($email);
        if (! $user || ! $user->authenticate($password)) {
            return null;
        }

        return $user;
    }

    public function changePassword(int $id, string $newPassword): UserEntityInterface
    {
        $this->validatePassword($newPassword);

        return $this->processUserById($id, function ($user) use ($newPassword) {
            $user->updatePassword($newPassword);
            return $this->repository->save($user);
        });
    }


    // Salva entidade após alterações comportamentais

    /**
     * Cria usuário usando DTO puro (SRP + Tell Don't Ask)
     */
    public function createUser(CreateUserDataDTO $data): UserEntityInterface
    {
        // Validações de domínio usando dados do DTO
        $this->validateEmail($data->email);
        $this->validateEmailAvailabilityOrThrow($data->email);
        $this->validatePassword($data->password);
        $this->validateRole($data->role);

        // Criação da entidade usando propriedades readonly do DTO
        $user = new UserEntity(
            $data->name,
            $data->email,
            $data->password,
            $data->role,
            'active'
        );

        return $this->repository->save($user);
    }

    /**
     * Desabilita usuário se inativo há muito tempo - Tell, Don't Ask
     */
    public function deactivateInactiveUsers(int $daysInactive = 90): array
    {
        return $this->processAllUsers(function ($user) {
            if ($user->isActive() && $user->needsPasswordChange()) {
                $user->deactivate();
                $this->saveUser($user);
                return $user;
            }
            return null;
        });
    }

    public function deactivateUser(int $id): UserEntityInterface
    {
        return $this->processUserById($id, function ($user) {
            $user->deactivate();
            return $this->repository->save($user);
        });
    }

    public function deleteUser(int $id): bool
    {
        try {
            return $this->processUserById($id, function ($user) {
                return $this->repository->delete($user);
            });
        } catch (BusinessLogicExceptionAbstract $e) {
            return false;
        }
    }

    /**
     * Força mudança de senha para usuários que precisam - Tell, Don't Ask
     */
    public function enforcePasswordChange(array $userIds): array
    {
        $updatedUsers = [];

        foreach ($userIds as $userId) {
            try {
                $user = $this->processUserById($userId, function ($user) {
                    if ($user->needsPasswordChange()) {
                        $user->deactivate();
                        return $this->saveUser($user);
                    }
                    return null;
                });

                if ($user !== null) {
                    $updatedUsers[] = $user;
                }
            } catch (InvalidArgumentException $e) {
                // Usuário não encontrado, continua para o próximo
                continue;
            }
        }

        return $updatedUsers;
    }

    /**
     * Gera relatório de estatísticas dos usuários
     */
    public function generateUserStatistics(): array
    {
        $totalUsers = $this->repository->count();
        $activeUsers = $this->repository->count(['status' => 'active']);
        $adminUsers = $this->repository->count(['role' => 'admin']);

        return [
            'total' => $totalUsers,
            'active' => $activeUsers,
            'inactive' => $totalUsers - $activeUsers,
            'admins' => $adminUsers,
            'regular_users' => $totalUsers - $adminUsers,
            'activation_rate' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 2) : 0,
        ];
    }

    /**
     * Processa todos os usuários com uma ação específica
     */
    public function processAllUsers(callable $action): array
    {
        $users = $this->repository->findAll();
        $results = [];

        foreach ($users as $user) {
            $results[] = $action($user);
        }

        return $results;
    }

    // ✅ TELL, DON'T ASK: High-level behavioral methods

    /**
     * Processa usuário por ID com ação específica
     */
    public function processUserById(int $id, callable $action)
    {
        $user = $this->repository->find($id);
        if (! $user instanceof UserEntityInterface) {
            throw new BusinessLogicExceptionAbstract("Usuário com ID {$id} não encontrado");
        }

        return $action($user);
    }

    /**
     * Processa todos os usuários de uma função específica
     */
    public function processUsersByRole(string $role, callable $action): array
    {
        $users = $this->repository->findByRole($role);
        $results = [];

        foreach ($users as $user) {
            $results[] = $action($user);
        }

        return $results;
    }

    /**
     * Promove usuário para admin - Tell, Don't Ask
     */
    public function promoteToAdmin(int $userId): ?UserEntityInterface
    {
        try {
            return $this->processUserById($userId, function ($user) {
                if ($user->isAdmin()) {
                    return $user;
                }

                $user->updateProfile(['role' => 'admin']);
                return $this->saveUser($user);
            });
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    public function saveUser(UserEntityInterface $user): UserEntityInterface
    {
        return $this->repository->save($user);
    }

    public function searchUsersByName(string $name): array
    {
        return $this->repository->searchByName($name);
    }

    /**
     * Atualiza usuário usando DTO puro (SRP + Tell Don't Ask)
     */
    public function updateUser(int $id, UpdateUserDataDTO $data): UserEntityInterface
    {
        return $this->processUserById($id, function ($user) use ($data, $id) {
            // Validações usando propriedades readonly do DTO
            if ($data->email !== null) {
                $this->validateEmail($data->email);
                if ($data->email !== $user->email) {
                    $this->validateEmailAvailabilityOrThrow($data->email, $id);
                }
            }

            if ($data->password !== null) {
                $this->validatePassword($data->password);
            }

            if ($data->role !== null) {
                $this->validateRole($data->role);
            }

            $dataArray = $data->toArray();

            // Profile updates
            $profileFields = ['name', 'email', 'role'];
            $profileData = array_intersect_key($dataArray, array_flip($profileFields));
            if (! empty($profileData)) {
                $user->updateProfile($profileData);
            }

            // Password update
            if ($data->password !== null) {
                $user->updatePassword($data->password);
            }

            // Status changes
            if ($data->status !== null) {
                if ($data->status === 'active') {
                    $user->activate();
                } elseif ($data->status === 'inactive') {
                    $user->deactivate();
                }
            }

            return $this->repository->save($user);
        });
    }

    /**
     * Valida se email está disponível para uso
     */
    public function validateEmailAvailability(string $email, ?int $excludeId = null): bool
    {
        $existingUser = $this->repository->findByEmail($email);
        if (! $existingUser) {
            return true;
        }
        return $excludeId !== null && $existingUser->getId() === $excludeId;
    }

    /**
     * Valida capacidade do sistema (verifica se pode criar novos usuários)
     */
    public function validateSystemCapacity(int $maxUsers = 1000): bool
    {
        return $this->repository->count() < $maxUsers;
    }

    protected function extractEntityData(object $entity): array
    {
        if (! $entity instanceof UserEntityInterface) {
            throw new BusinessLogicExceptionAbstract('Entidade deve implementar UserEntityInterface');
        }

        return [
            'name' => $entity->name,
            'email' => $entity->email,
            'role' => $entity->role,
            'status' => $entity->status,
        ];
    }

    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    private function validateEmail(string $email): void
    {
        if (! $this->emailValidator->validate($email)) {
            throw new ValidationException('Formato de email inválido: ' . $this->emailValidator->getErrorMessage());
        }
    }

    private function validateEmailAvailabilityOrThrow(string $email, ?int $excludeId = null): void
    {
        if (! $this->validateEmailAvailability($email, $excludeId)) {
            throw new ValidationException("Email '{$email}' já está em uso");
        }
    }

    private function validatePassword(string $password): void
    {
        if (strlen($password) < 6) {
            throw new ValidationException('Senha deve ter pelo menos 6 caracteres');
        }
    }

    private function validateRole(string $role): void
    {
        $validRoles = ['admin', 'user', 'moderator'];
        if (! in_array($role, $validRoles, true)) {
            throw new ValidationException('Função inválida. Deve ser uma das: ' . implode(', ', $validRoles));
        }
    }

    private function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
