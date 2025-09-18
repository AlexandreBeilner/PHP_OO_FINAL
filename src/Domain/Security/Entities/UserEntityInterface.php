<?php

declare(strict_types=1);

namespace App\Domain\Security\Entities;

use App\Domain\Common\Entities\Behaviors\TimestampableBehaviorInterface;
use App\Domain\Common\Entities\Behaviors\UuidableBehaviorInterface;

interface UserEntityInterface extends TimestampableBehaviorInterface, UuidableBehaviorInterface
{
    public function getEmail(): string;

    public function getId(): int;

    public function getName(): string;

    public function getPassword(): string;

    public function getRole(): string;

    public function getStatus(): string;

    public function hasRole(string $role): bool;

    public function isActive(): bool;

    public function setEmail(string $email): self;

    public function setId(int $id): self;

    public function setName(string $name): self;

    public function setPassword(string $password): self;

    public function setRole(string $role): self;

    public function setStatus(string $status): self;

    public function verifyPassword(string $password): bool;
}
