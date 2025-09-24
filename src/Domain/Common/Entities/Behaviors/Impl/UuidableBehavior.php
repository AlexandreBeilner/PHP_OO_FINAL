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

    /**
     * Gera um novo UUID para a entidade
     */
    public function generateUuid(): self
    {
        $this->uuid = $this->createUuid();
        return $this;
    }

    /**
     * Verifica se possui UUID
     */
    public function hasUuid(): bool
    {
        return $this->uuid !== null;
    }

    /**
     * Verifica se o UUID é válido (formato correto)
     */
    public function hasValidUuid(): bool
    {
        if (!$this->hasUuid()) {
            return false;
        }
        
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $this->uuid) === 1;
    }

    /**
     * Verifica se o UUID corresponde a outro UUID
     */
    public function matchesUuid(string $otherUuid): bool
    {
        return $this->hasUuid() && $this->uuid === $otherUuid;
    }

    /**
     * Regenera o UUID (cria um novo)
     */
    public function regenerateUuid(): self
    {
        return $this->generateUuid();
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
}
