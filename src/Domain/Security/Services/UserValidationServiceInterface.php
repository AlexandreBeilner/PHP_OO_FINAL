<?php

declare(strict_types=1);

namespace App\Domain\Security\Services;

use App\Application\Common\DTOs\User\Impl\CreateUserRequestDTO;
use App\Application\Common\DTOs\User\Impl\UpdateUserRequestDTO;
use App\Application\Common\DTOs\Impl\ValidationResult;
use Psr\Http\Message\ServerRequestInterface;

interface UserValidationServiceInterface
{
    public function validateCreateUserRequest(ServerRequestInterface $request): CreateUserRequestDTO;
    public function validateUpdateUserRequest(ServerRequestInterface $request): UpdateUserRequestDTO;
    public function validateUserId(int $id): ValidationResult;
}
