<?php

declare(strict_types=1);

namespace App\Domain\Security\Entities;

use App\Domain\Common\Entities\Behaviors\TimestampableBehaviorInterface;
use App\Domain\Common\Entities\Behaviors\UuidableBehaviorInterface;

interface UserEntityInterface extends TimestampableBehaviorInterface, UuidableBehaviorInterface
{

    public function activate(): self;

    public function authenticate(string $password): bool;

    public function canBePromotedToAdmin(): bool;

    public function canChangeEmailTo(string $newEmail): bool;

    public function canPerform(string $action): bool;

    public function deactivate(): self;

    public function getId(): int;

    public function hasCompleteProfile(): bool;

    public function hasRole(string $role): bool;

    public function isActive(): bool;

    public function isAdmin(): bool;

    public function isSameUser(UserEntityInterface $other): bool;

    public function needsPasswordChange(): bool;

    public function updatePassword(string $newPassword): self;

    public function updateProfile(array $profileData): self;

    public function verifyPassword(string $password): bool;
}
