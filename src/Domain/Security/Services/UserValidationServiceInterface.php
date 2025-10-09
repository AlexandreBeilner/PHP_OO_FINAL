<?php

declare(strict_types=1);

namespace App\Domain\Security\Services;

use App\Application\Shared\DTOs\Impl\ValidationResult;
use App\Domain\Security\Commands\Impl\CreateUserCommand;
use App\Domain\Security\Commands\Impl\UpdateUserCommand;
use App\Domain\Security\DTOs\Impl\CreateUserDataDTO;
use App\Domain\Security\DTOs\Impl\UpdateUserDataDTO;
use Psr\Http\Message\ServerRequestInterface;

interface UserValidationServiceInterface
{
    public function validateCreateUserCommand(ServerRequestInterface $request): CreateUserCommand;

    // ✅ COMMAND PATTERN: Tell, Don't Ask

    public function validateCreateUserRequest(ServerRequestInterface $request): CreateUserDataDTO;

    public function validateUpdateUserCommand(ServerRequestInterface $request): UpdateUserCommand;

    // ✅ COMMAND PATTERN: Tell, Don't Ask

    public function validateUpdateUserRequest(ServerRequestInterface $request): UpdateUserDataDTO;

    public function validateUserId(int $id): ValidationResult;
}
