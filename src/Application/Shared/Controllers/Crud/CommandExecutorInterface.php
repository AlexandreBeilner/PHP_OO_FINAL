<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud;

interface CommandExecutorInterface
{
    public function execute($command);
    
    public function executeWithId($command, int $id);
    
    public function deleteById(int $id): bool;
    
    public function findById(int $id);
    
    public function findAll();
}
