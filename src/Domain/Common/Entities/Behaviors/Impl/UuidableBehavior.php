<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities\Behaviors\Impl;

use App\Domain\Common\Entities\Behaviors\UuidableBehaviorInterface;

final class UuidableBehavior implements UuidableBehaviorInterface
{
    private ?string $uuid = null;

    public function __construct(?string $uuid = null)
    {
        $this->uuid = $uuid;
    }

    public function generateUuid(): self
    {
        $this->uuid = $this->createUuid();
        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function hasUuid(): bool
    {
        return $this->uuid !== null;
    }

    private function createUuid(): string
    {
        if (function_exists('uuid_create')) {
            return uuid_create(UUID_TYPE_RANDOM);
        }

        // Fallback para sistemas sem extensão UUID
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Versão 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Bits de variante

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
