<?php

declare(strict_types=1);

namespace App\Application\Common\DTOs;

interface BaseDTOInterface
{
    public function __get(string $name);

    public function __set(string $name, $value): void;

    public function __isset(string $name): bool;

    public function toArray(): array;

    public function toJson(): string;
}
