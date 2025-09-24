<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\Factories\Impl;

use App\Application\Shared\Controllers\Crud\CommandExecutorInterface;
use App\Domain\Security\Services\UserServiceInterface;

final class UserCommandExecutor implements CommandExecutorInterface
{
    private UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function execute($command)
    {
        return $command->executeWith($this->userService);
    }

    public function executeWithId($command, int $id)
    {
        return $command->executeWithUserId($this->userService, $id);
    }

    public function deleteById(int $id): bool
    {
        return $this->userService->deleteUser($id);
    }

    public function findById(int $id)
    {
        return $this->userService->processUserById($id, fn($user) => $user);
    }

    public function findAll()
    {
        return $this->userService->processAllUsers(fn($user) => $user);
    }
}
