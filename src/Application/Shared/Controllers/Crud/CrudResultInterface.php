<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud;

interface CrudResultInterface
{
    public function getCode(): int;

    public function getData();

    public function getMessage(): string;

    public function getMeta(): array;

    public function hasMeta(): bool;
}
