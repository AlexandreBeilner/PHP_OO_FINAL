<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud;

interface CommandExecutorInterface
{
    public function deleteById(int $id): bool;

    public function execute($command);

    public function executeWithId($command, int $id);

    public function findAll();

    public function findById(int $id);
}
