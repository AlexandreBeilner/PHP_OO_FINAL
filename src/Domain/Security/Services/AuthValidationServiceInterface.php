<?php

declare(strict_types=1);

namespace App\Domain\Security\Services;

use App\Application\Shared\DTOs\Impl\ValidationResult;
use App\Domain\Auth\DTOs\Impl\ChangePasswordDataDTO;
use App\Domain\Auth\DTOs\Impl\LoginDataDTO;
use Psr\Http\Message\ServerRequestInterface;

interface AuthValidationServiceInterface
{
    public function extractUserIdFromRequest(ServerRequestInterface $request, array $args): int;

    public function validateChangePasswordRequest(ServerRequestInterface $request): ChangePasswordDataDTO;

    public function validateLoginRequest(ServerRequestInterface $request): LoginDataDTO;

    public function validateUserId(int $id): ValidationResult;
}
