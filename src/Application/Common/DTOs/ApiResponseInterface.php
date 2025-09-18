<?php

declare(strict_types=1);

namespace App\Application\Common\DTOs;

interface ApiResponseInterface
{
    public function addMeta(string $key, $value): self;

    public function getCode(): int;

    public function getData();

    public function getMessage(): string;

    public function getMeta(): array;

    public function isSuccess(): bool;

    public function toArray(): array;

    public function toJson(): string;
}
