<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities\Behaviors\Impl;

use App\Domain\Common\Entities\Behaviors\SoftDeletableBehaviorInterface;
use DateTime;

final class SoftDeletableBehavior implements SoftDeletableBehaviorInterface
{
    private ?DateTime $deletedAt = null;

    public function __construct(?DateTime $deletedAt = null)
    {
        $this->deletedAt = $deletedAt;
    }

    /**
     * Formata a data de exclusão
     */
    public function getDeletedAtFormatted(string $format = 'Y-m-d H:i:s'): ?string
    {
        return $this->deletedAt ? $this->deletedAt->format($format) : null;
    }

    /**
     * Verifica se está excluído
     */
    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    /**
     * Restaura o item (remove a exclusão lógica)
     */
    public function restore(): self
    {
        $this->deletedAt = null;
        return $this;
    }

    /**
     * Realiza exclusão lógica
     */
    public function softDelete(): self
    {
        if (!$this->isDeleted()) {
            $this->deletedAt = new DateTime();
        }
        return $this;
    }

    /**
     * Verifica se foi excluído recentemente (nas últimas 24 horas)
     */
    public function wasDeletedRecently(): bool
    {
        if (!$this->isDeleted()) {
            return false;
        }
        
        $oneDayAgo = (new DateTime())->modify('-1 day');
        return $this->deletedAt > $oneDayAgo;
    }

    /**
     * Calcula dias desde a exclusão
     */
    public function getDaysSinceDeletion(): int
    {
        if (!$this->isDeleted()) {
            return 0;
        }
        
        $now = new DateTime();
        return (int) $this->deletedAt->diff($now)->days;
    }

    /**
     * Verifica se pode ser restaurado (baseado em regras de negócio)
     */
    public function canBeRestored(): bool
    {
        if (!$this->isDeleted()) {
            return false;
        }
        
        // Exemplo: pode restaurar se foi excluído há menos de 30 dias
        return $this->getDaysSinceDeletion() <= 30;
    }
}
