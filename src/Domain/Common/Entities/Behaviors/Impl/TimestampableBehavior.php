<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities\Behaviors\Impl;

use App\Domain\Common\Entities\Behaviors\TimestampableBehaviorInterface;
use DateTime;

final class TimestampableBehavior implements TimestampableBehaviorInterface
{
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(?DateTime $createdAt = null, ?DateTime $updatedAt = null)
    {
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
    }

    /**
     * Formata a data de criação
     */
    public function getCreatedAtFormatted(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->createdAt->format($format);
    }

    /**
     * Formata a data de atualização
     */
    public function getUpdatedAtFormatted(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->updatedAt->format($format);
    }

    /**
     * Verifica se foi criado recentemente (nas últimas 24 horas)
     */
    public function wasCreatedRecently(): bool
    {
        $oneDayAgo = (new DateTime())->modify('-1 day');
        return $this->createdAt > $oneDayAgo;
    }

    /**
     * Verifica se foi atualizado recentemente (nas últimas 24 horas)
     */
    public function wasUpdatedRecently(): bool
    {
        $oneDayAgo = (new DateTime())->modify('-1 day');
        return $this->updatedAt > $oneDayAgo;
    }

    /**
     * Verifica se nunca foi atualizado (created_at == updated_at)
     */
    public function neverUpdated(): bool
    {
        return $this->createdAt->getTimestamp() === $this->updatedAt->getTimestamp();
    }

    /**
     * Calcula idade em dias desde a criação
     */
    public function getAgeInDays(): int
    {
        $now = new DateTime();
        return (int) $this->createdAt->diff($now)->days;
    }

    /**
     * Calcula dias desde a última atualização
     */
    public function getDaysSinceUpdate(): int
    {
        $now = new DateTime();
        return (int) $this->updatedAt->diff($now)->days;
    }

    /**
     * Atualiza o timestamp de modificação para agora
     */
    public function touch(): self
    {
        $this->updatedAt = new DateTime();
        return $this;
    }
}
