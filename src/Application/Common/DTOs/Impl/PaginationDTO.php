<?php

declare(strict_types=1);

namespace App\Application\Common\DTOs\Impl;

use App\Application\Common\DTOs\PaginationDTOInterface;
use InvalidArgumentException;

final class PaginationDTO implements PaginationDTOInterface
{
    private int $limit;
    private int $page;
    private int $pages;
    private int $total;

    public function __construct(int $page, int $limit, int $total)
    {
        $this->page = $page;
        $this->limit = $limit;
        $this->total = $total;
        $this->pages = ceil($total / $limit);

        $this->validate();
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getNextPage(): ?int
    {
        return $this->hasNextPage() ? $this->page + 1 : null;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPages(): int
    {
        return $this->pages;
    }

    public function getPreviousPage(): ?int
    {
        return $this->hasPreviousPage() ? $this->page - 1 : null;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function hasNextPage(): bool
    {
        return $this->page < $this->pages;
    }

    public function hasPreviousPage(): bool
    {
        return $this->page > 1;
    }

    private function validate(): void
    {
        if ($this->page < 1) {
            throw new InvalidArgumentException('Página deve ser maior que 0');
        }

        if ($this->limit < 1) {
            throw new InvalidArgumentException('Limite deve ser maior que 0');
        }

        if ($this->total < 0) {
            throw new InvalidArgumentException('Total deve ser maior ou igual a 0');
        }
    }
}
