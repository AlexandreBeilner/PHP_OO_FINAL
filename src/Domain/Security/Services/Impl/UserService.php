<?php

declare(strict_types=1);

namespace App\Domain\Security\Services\Impl;

use App\Domain\Common\Entities\Behaviors\Impl\TimestampableBehavior;
use App\Domain\Common\Entities\Behaviors\Impl\UuidableBehavior;
use App\Domain\Common\Exceptions\Impl\BusinessLogicExceptionAbstract;
use App\Domain\Common\Exceptions\Impl\ValidationException;
use App\Domain\Common\Services\Impl\AbstractService;
use App\Domain\Common\Validators\EmailValidatorInterface;
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
        return $this->updateUser($id, ['status' => 'active']);
    }

    public function authenticateUser(string $email, string $password): ?UserEntityInterface
    {
        $user = $this->getUserByEmail($email);
        if (! $user) {
            return null;
        }

        if ($this->verifyPassword($password, $user->getPassword())) {
            return $user;
        }

        return null;
    }

    public function changePassword(int $id, string $newPassword): UserEntityInterface
    {
        $this->validatePassword($newPassword);
        return $this->updateUser($id, ['password' => $newPassword]);
    }

    public function createUser(string $name, string $email, string $password, string $role = 'user'): UserEntityInterface
    {
        $this->validateEmail($email);
        $this->validateEmailAvailability($email);
        $this->validateUserData($name, $email, $password, $role);

        $timestampableBehavior = new TimestampableBehavior();
        $uuidableBehavior = new UuidableBehavior();

        $user = new UserEntity(
            $name,
            $email,
            $password,
            $role,
            'active'
        );

        return $this->repository->save($user);
    }

    public function deactivateUser(int $id): UserEntityInterface
    {
        return $this->updateUser($id, ['status' => 'inactive']);
    }

    public function deleteUser(int $id): bool
    {
        $user = $this->getUserById($id);
        if (! $user) {
            return false;
        }

        return $this->repository->delete($user);
    }

    public function getActiveUsers(): array
    {
        return $this->repository->findActiveUsers();
    }

    public function getAllUsers(): array
    {
        return $this->repository->findAll();
    }

    public function getInactiveUsers(): array
    {
        return $this->repository->findInactiveUsers();
    }

    public function getUserByEmail(string $email): ?UserEntityInterface
    {
        return $this->repository->findByEmail($email);
    }

    public function getUserById(int $id): ?UserEntityInterface
    {
        // Usar o método find do EntityRepository diretamente através do parent
        $user = $this->repository->find($id);
        return $user instanceof UserEntityInterface ? $user : null;
    }

    public function getUserCount(): int
    {
        return $this->repository->count();
    }

    public function getUserCountByRole(string $role): int
    {
        return $this->repository->count(['role' => $role]);
    }

    public function getUsersByRole(string $role): array
    {
        return $this->repository->findByRole($role);
    }

    public function isEmailAvailable(string $email, ?int $excludeId = null): bool
    {
        $existingUser = $this->getUserByEmail($email);
        if (! $existingUser) {
            return true;
        }

        return $excludeId !== null && $existingUser->getId() === $excludeId;
    }

    public function searchUsersByName(string $name): array
    {
        return $this->repository->searchByName($name);
    }

    public function updateUser(int $id, array $data): UserEntityInterface
    {
        $user = $this->getUserById($id);
        if (! $user) {
            throw new BusinessLogicExceptionAbstract("Usuário com ID {$id} não encontrado");
        }

        if (isset($data['email'])) {
            $this->validateEmail($data['email']);
            if ($data['email'] !== $user->getEmail()) {
                $this->validateEmailAvailability($data['email'], $id);
            }
        }

        if (isset($data['name'])) {
            $user->setName($data['name']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['role'])) {
            $user->setRole($data['role']);
        }
        if (isset($data['status'])) {
            $user->setStatus($data['status']);
        }
        if (isset($data['password'])) {
            $user->setPassword($data['password']);
        }

        $user->touch(); // Atualizar timestamp

        return $this->repository->save($user);
    }

    protected function extractEntityData(object $entity): array
    {
        if (! $entity instanceof UserEntityInterface) {
            throw new \InvalidArgumentException('Entidade deve implementar UserEntityInterface');
        }

        return [
            'name' => $entity->getName(),
            'email' => $entity->getEmail(),
            'password' => $entity->getPassword(),
            'role' => $entity->getRole(),
            'status' => $entity->getStatus(),
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

    private function validateEmailAvailability(string $email, ?int $excludeId = null): void
    {
        if (! $this->isEmailAvailable($email, $excludeId)) {
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

    private function validateUserData(string $name, string $email, string $password, string $role): void
    {
        if (empty(trim($name))) {
            throw new ValidationException('Nome não pode estar vazio');
        }

        if (strlen($name) < 2) {
            throw new ValidationException('Nome deve ter pelo menos 2 caracteres');
        }

        $this->validatePassword($password);
        $this->validateRole($role);
    }

    private function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
