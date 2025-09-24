<?php

declare(strict_types=1);

namespace App\Application\Shared\DTOs\Impl;

use App\Application\Shared\DTOs\ApiResponseInterface;

final class ApiResponse implements ApiResponseInterface
{
    private int $code;
    private $data;
    private string $message;
    private array $meta = [];
    private bool $success;

    public function __construct(bool $success, $data, string $message, int $code = 200, array $meta = [])
    {
        $this->success = $success;
        $this->data = $data;
        $this->message = $message;
        $this->code = $code;
        $this->meta = $meta;
    }

    public function addMeta(string $key, $value): self
    {
        $this->meta[$key] = $value;
        return $this;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
            'code' => $this->code,
            'meta' => $this->meta,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
