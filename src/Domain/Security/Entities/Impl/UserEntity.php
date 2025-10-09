<?php

declare(strict_types=1);

namespace App\Domain\Security\Entities\Impl;

use App\Domain\Common\Entities\Behaviors\Impl\TimestampableBehavior;
use App\Domain\Common\Entities\Behaviors\Impl\UuidableBehavior;
use App\Domain\Common\Entities\Behaviors\TimestampableBehaviorInterface;
use App\Domain\Common\Entities\Behaviors\UuidableBehaviorInterface;
use App\Domain\Security\Entities\UserEntityInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass="App\Domain\Security\Repositories\Impl\UserRepository")
 * @ORM\Table(name="users")
 */
class UserEntity implements UserEntityInterface, JsonSerializable
{
    /**
     * @ORM\Column(type="datetime")
     */
    public DateTime $createdAt;
    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    public string $email;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    public int $id;
    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $name;
    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $password;
    /**
     * @ORM\Column(type="string", length=50)
     */
    public string $role;
    /**
     * @ORM\Column(type="string", length=20)
     */
    public string $status;
    /**
     * @ORM\Column(type="datetime")
     */
    public DateTime $updatedAt;
    /**
     * @ORM\Column(type="string", length=36, nullable=true)
     */
    public ?string $uuid = null;
    private TimestampableBehaviorInterface $timestampableBehavior;
    private UuidableBehaviorInterface $uuidableBehavior;

    public function __construct(
        string $name,
        string $email,
        string $password,
        string $role = 'user',
        string $status = 'active',
        ?string $uuid = null
    ) {
        $this->timestampableBehavior = new TimestampableBehavior();
        $this->uuidableBehavior = new UuidableBehavior();

        $this->id = 0; // Será definido pelo banco de dados
        $this->name = $name;
        $this->email = $email;
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->role = $role;
        $this->status = $status;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        if ($uuid) {
            $this->uuid = $uuid;
        } else {
            $this->generateUuid();
        }
    }

    /**
     * Ativa o usuário
     */
    public function activate(): self
    {
        $this->status = 'active';
        $this->touchEntity();
        return $this;
    }

    /**
     * Autentica usuário com senha
     */
    public function authenticate(string $password): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        return $this->verifyPassword($password);
    }

    /**
     * Verifica se o usuário pode ser promovido a administrador
     */
    public function canBePromotedToAdmin(): bool
    {
        return $this->isActive() && ! $this->isAdmin();
    }

    /**
     * Verifica se o email pode ser alterado para o novo valor
     */
    public function canChangeEmailTo(string $newEmail): bool
    {
        return $this->email !== $newEmail && filter_var($newEmail, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Verifica se usuário pode executar ação
     */
    public function canPerform(string $action): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        // Lógica de permissões baseada em roles
        switch ($this->role) {
            case 'admin':
                return true; // Admin pode tudo
            case 'user':
                return in_array($action, ['view', 'edit_own']);
            default:
                return false;
        }
    }

    /**
     * Desativa o usuário
     */
    public function deactivate(): self
    {
        $this->status = 'inactive';
        $this->touchEntity();
        return $this;
    }

    public function generateUuid(): self
    {
        $this->uuidableBehavior->generateUuid();
        // Acessar UUID através do behavior após geração
        if ($this->uuidableBehavior->hasUuid()) {
            // UUID foi gerado com sucesso
            $this->uuid = $this->createUuid();
        }
        return $this;
    }

    public function getAgeInDays(): int
    {
        return $this->timestampableBehavior->getAgeInDays();
    }

    public function getCreatedAtFormatted(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->timestampableBehavior->getCreatedAtFormatted($format);
    }

    // Métodos comportamentais

    public function getDaysSinceUpdate(): int
    {
        return $this->timestampableBehavior->getDaysSinceUpdate();
    }

    /**
     * Exceção: getId() necessário para compatibilidade com ORM/Doctrine
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getUpdatedAtFormatted(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->timestampableBehavior->getUpdatedAtFormatted($format);
    }

    /**
     * Verifica se o perfil do usuário está completo
     */
    public function hasCompleteProfile(): bool
    {
        return ! empty($this->name) && ! empty($this->email) && ! empty($this->role);
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasUuid(): bool
    {
        return $this->uuidableBehavior->hasUuid();
    }

    public function hasValidUuid(): bool
    {
        return $this->uuidableBehavior->hasValidUuid();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verifica se o usuário possui uma função específica
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Verifica se é o mesmo usuário
     */
    public function isSameUser(UserEntityInterface $other): bool
    {
        return $this->id === $other->getId();
    }

    /**
     * Serialização JSON usando acesso direto às propriedades
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'uuid' => $this->uuid,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }

    public function matchesUuid(string $otherUuid): bool
    {
        return $this->uuidableBehavior->matchesUuid($otherUuid);
    }

    // Implementação dos métodos TimestampableBehaviorInterface

    /**
     * Verifica se a senha precisa ser alterada (regra de negócio)
     */
    public function needsPasswordChange(): bool
    {
        // Exemplo: senha deve ser alterada após 90 dias
        $ninetyDaysAgo = (new DateTime())->modify('-90 days');
        return $this->updatedAt < $ninetyDaysAgo;
    }

    public function neverUpdated(): bool
    {
        return $this->timestampableBehavior->neverUpdated();
    }

    public function regenerateUuid(): self
    {
        $this->uuidableBehavior->regenerateUuid();
        $this->uuid = $this->createUuid();
        return $this;
    }

    public function touch(): self
    {
        $this->timestampableBehavior->touch();
        $this->updatedAt = new DateTime();
        return $this;
    }

    /**
     * Atualiza senha do usuário
     */
    public function updatePassword(string $newPassword): self
    {
        $this->password = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->touchEntity();
        return $this;
    }

    /**
     * Atualiza perfil do usuário
     */
    public function updateProfile(array $profileData): self
    {
        if (isset($profileData['name'])) {
            $this->name = $profileData['name'];
        }
        if (isset($profileData['email'])) {
            $this->email = $profileData['email'];
        }
        if (isset($profileData['role'])) {
            $this->role = $profileData['role'];
        }

        $this->touchEntity();
        return $this;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    // Implementação dos métodos UuidableBehaviorInterface

    public function wasCreatedRecently(): bool
    {
        return $this->timestampableBehavior->wasCreatedRecently();
    }

    public function wasUpdatedRecently(): bool
    {
        return $this->timestampableBehavior->wasUpdatedRecently();
    }

    /**
     * Cria um UUID versão 4 (aleatório)
     */
    private function createUuid(): string
    {
        if (function_exists('uuid_create')) {
            return uuid_create(UUID_TYPE_RANDOM);
        }

        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Método interno da entidade para atualizar timestamp
     */
    private function touchEntity(): self
    {
        $this->updatedAt = new DateTime();
        $this->timestampableBehavior = new TimestampableBehavior($this->createdAt, $this->updatedAt);
        return $this;
    }
}
