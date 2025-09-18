<?php

declare(strict_types=1);

namespace App\Domain\Common\Exceptions;

interface BaseExceptionInterface
{
    public function addContext(string $key, $value): self;

    public function getContext(): array;
}
