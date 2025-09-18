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
    private DateTime $createdAt;
    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private string $email;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $password;
    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $role;
    /**
     * @ORM\Column(type="string", length=20)
     */
    private string $status;
    private TimestampableBehaviorInterface $timestampableBehavior;
    /**
     * @ORM\Column(type="datetime")
     */
    private DateTime $updatedAt;
    /**
     * @ORM\Column(type="string", length=36, nullable=true)
     */
    private ?string $uuid = null;
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
        $this->uuid = $uuid ?? $this->generateUuid()->getUuid();
    }

    // Getters e Setters básicos

    public function generateUuid(): self
    {
        $this->uuidableBehavior->generateUuid();
        $this->uuid = $this->uuidableBehavior->getUuid();
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        if (!isset($this->timestampableBehavior)) {
            $this->timestampableBehavior = new TimestampableBehavior();
        }
        return $this->timestampableBehavior->getCreatedAt();
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        $this->timestampableBehavior = new TimestampableBehavior($createdAt, $this->updatedAt);
        return $this;
    }

    public function getCreatedAtFormatted(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->timestampableBehavior->getCreatedAtFormatted($format);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        return $this;
    }

    // Métodos de negócio

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    // TimestampableInterface delegation

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getUpdatedAt(): DateTime
    {
        if (!isset($this->timestampableBehavior)) {
            $this->timestampableBehavior = new TimestampableBehavior();
        }
        return $this->timestampableBehavior->getUpdatedAt();
    }

    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        $this->timestampableBehavior = new TimestampableBehavior($this->createdAt, $updatedAt);
        return $this;
    }

    public function getUpdatedAtFormatted(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->timestampableBehavior->getUpdatedAtFormatted($format);
    }

    public function getUuid(): ?string
    {
        if (!isset($this->uuidableBehavior)) {
            $this->uuidableBehavior = new UuidableBehavior();
        }
        return $this->uuidableBehavior->getUuid();
    }

    public function setUuid(?string $uuid): self
    {
        $this->uuid = $uuid;
        $this->uuidableBehavior = new UuidableBehavior($uuid);
        return $this;
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    // UuidableInterface delegation

    public function hasUuid(): bool
    {
        return $this->uuidableBehavior->hasUuid();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function touch(): self
    {
        $this->updatedAt = new DateTime();
        $this->timestampableBehavior = new TimestampableBehavior($this->createdAt, $this->updatedAt);
        return $this;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'role' => $this->getRole(),
            'uuid' => $this->getUuid(),
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $this->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
