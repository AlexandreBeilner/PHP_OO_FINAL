<?php

declare(strict_types=1);

namespace App\Application\Common\DTOs\Impl;

use App\Application\Common\DTOs\BaseDTOInterface;

abstract class AbstractBaseDTO implements BaseDTOInterface
{
    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this->validate();
    }

    public function __get(string $name)
    {
        return $this->data[$name] ?? null;
    }

    public function __set(string $name, $value): void
    {
        $this->data[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function toJson(): string
    {
        return json_encode($this->data, JSON_THROW_ON_ERROR);
    }

    abstract protected function validate(): void;
}
