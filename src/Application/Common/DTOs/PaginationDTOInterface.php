<?php

declare(strict_types=1);

namespace App\Application\Common\DTOs;

interface PaginationDTOInterface
{
    public function getLimit(): int;

    public function getNextPage(): ?int;

    public function getOffset(): int;

    public function getPage(): int;

    public function getPages(): int;

    public function getPreviousPage(): ?int;

    public function getTotal(): int;

    public function hasNextPage(): bool;

    public function hasPreviousPage(): bool;
}
