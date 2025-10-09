<?php

declare(strict_types=1);

namespace App\Domain\Products\Entities\Impl;

use App\Domain\Common\Entities\Behaviors\Impl\TimestampableBehavior;
use App\Domain\Common\Entities\Behaviors\Impl\UuidableBehavior;
use App\Domain\Common\Entities\Behaviors\TimestampableBehaviorInterface;
use App\Domain\Common\Entities\Behaviors\UuidableBehaviorInterface;
use App\Domain\Products\Entities\ProductEntityInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass="App\Domain\Products\Repositories\Impl\ProductRepository")
 * @ORM\Table(name="products")
 */
class ProductEntity implements ProductEntityInterface, JsonSerializable
{
    /**
     * @ORM\Column(type="string", length=100)
     */
    public string $category;

    /**
     * @ORM\Column(type="datetime")
     */
    public DateTime $createdAt;

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
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    public float $price;

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
        float $price,
        string $category,
        string $status = 'draft',
        ?string $uuid = null
    ) {
        $this->timestampableBehavior = new TimestampableBehavior();
        $this->uuidableBehavior = new UuidableBehavior();

        $this->id = 0; // Será definido pelo banco de dados
        $this->name = $name;
        $this->price = $price;
        $this->category = $category;
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
     * Ativa o produto para venda
     */
    public function activate(): self
    {
        $this->status = 'active';
        $this->touchEntity();
        return $this;
    }

    /**
     * Verifica se produto pertence a uma categoria
     */
    public function belongsToCategory(string $category): bool
    {
        return $this->category === $category;
    }

    /**
     * Verifica se o produto pode ser vendido
     */
    public function canBeSold(): bool
    {
        return $this->isActive() && $this->isPriceValid();
    }

    /**
     * Desativa o produto
     */
    public function deactivate(): self
    {
        $this->status = 'inactive';
        $this->touchEntity();
        return $this;
    }

    public function generateUuid(): self
    {
        $this->initializeBehaviors();
        $this->uuidableBehavior->generateUuid();
        $this->uuid = $this->uuidableBehavior->getUuid();
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Obtém o ID do produto
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * Verifica se produto está ativo
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verifica se o preço é válido
     */
    public function isPriceValid(): bool
    {
        return $this->price > 0;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'price' => $this->price,
            'category' => $this->category,
            'status' => $this->status,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function setUuid(?string $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function touchEntity(): self
    {
        $this->initializeBehaviors();
        $this->timestampableBehavior->touch();
        $this->updatedAt = new DateTime();
        return $this;
    }

    /**
     * Atualiza o preço do produto com validação
     */
    public function updatePrice(float $newPrice): self
    {
        if ($newPrice <= 0) {
            throw new \InvalidArgumentException('Price must be greater than zero');
        }
        
        $this->price = $newPrice;
        $this->touchEntity();
        return $this;
    }

    // TimestampableBehaviorInterface methods
    public function getAgeInDays(): int
    {
        $this->initializeBehaviors();
        return $this->timestampableBehavior->getAgeInDays();
    }

    public function getCreatedAtFormatted(string $format = 'Y-m-d H:i:s'): string
    {
        $this->initializeBehaviors();
        return $this->timestampableBehavior->getCreatedAtFormatted($format);
    }

    public function getDaysSinceUpdate(): int
    {
        return $this->timestampableBehavior->getDaysSinceUpdate();
    }

    public function getUpdatedAtFormatted(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->timestampableBehavior->getUpdatedAtFormatted($format);
    }

    public function neverUpdated(): bool
    {
        return $this->timestampableBehavior->neverUpdated();
    }

    public function touch(): self
    {
        $this->initializeBehaviors();
        $this->timestampableBehavior->touch();
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function wasCreatedRecently(): bool
    {
        return $this->timestampableBehavior->wasCreatedRecently();
    }

    public function wasUpdatedRecently(): bool
    {
        return $this->timestampableBehavior->wasUpdatedRecently();
    }

    // UuidableBehaviorInterface methods
    public function hasUuid(): bool
    {
        return $this->uuidableBehavior->hasUuid();
    }

    public function hasValidUuid(): bool
    {
        return $this->uuidableBehavior->hasValidUuid();
    }

    public function matchesUuid(string $otherUuid): bool
    {
        return $this->uuidableBehavior->matchesUuid($otherUuid);
    }

    public function regenerateUuid(): self
    {
        $this->initializeBehaviors();
        $this->uuidableBehavior->regenerateUuid();
        $this->uuid = $this->uuidableBehavior->getUuid();
        return $this;
    }

    private function initializeBehaviors(): void
    {
        if (!isset($this->timestampableBehavior)) {
            $this->timestampableBehavior = new TimestampableBehavior();
        }
        if (!isset($this->uuidableBehavior)) {
            $this->uuidableBehavior = new UuidableBehavior($this->uuid);
        }
    }
}
