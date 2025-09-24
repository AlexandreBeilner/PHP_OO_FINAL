<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud;

interface CrudResultInterface
{
    public function getData();
    
    public function getMessage(): string;
    
    public function getCode(): int;
    
    public function hasMeta(): bool;
    
    public function getMeta(): array;
}
