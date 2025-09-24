<?php

declare(strict_types=1);

namespace App\Domain\Security\Services;

use App\Domain\Security\DTOs\Impl\CreateUserDTO;
use App\Domain\Security\DTOs\Impl\UpdateUserDTO;
use App\Domain\Security\Commands\Impl\CreateUserCommand;
use App\Domain\Security\Commands\Impl\UpdateUserCommand;
use App\Application\Shared\DTOs\Impl\ValidationResult;
use Psr\Http\Message\ServerRequestInterface;

interface UserValidationServiceInterface
{
    public function validateCreateUserRequest(ServerRequestInterface $request): CreateUserDTO;
    
    // ✅ COMMAND PATTERN: Tell, Don't Ask
    public function validateCreateUserCommand(ServerRequestInterface $request): CreateUserCommand;
    
    public function validateUpdateUserRequest(ServerRequestInterface $request): UpdateUserDTO;
    
    // ✅ COMMAND PATTERN: Tell, Don't Ask
    public function validateUpdateUserCommand(ServerRequestInterface $request): UpdateUserCommand;
    public function validateUserId(int $id): ValidationResult;
}
