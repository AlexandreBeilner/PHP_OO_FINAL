<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud\Impl;

use App\Application\Shared\Controllers\Crud\CrudResultInterface;

final class CrudResult implements CrudResultInterface
{
    private $data;
    private string $message;
    private int $code;
    private array $meta;

    public function __construct($data, string $message, int $code, array $meta = [])
    {
        $this->data = $data;
        $this->message = $message;
        $this->code = $code;
        $this->meta = $meta;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function hasMeta(): bool
    {
        return !empty($this->meta);
    }

    public function getMeta(): array
    {
        return $this->meta;
    }
}
