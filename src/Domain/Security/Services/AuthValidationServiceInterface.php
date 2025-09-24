<?php

declare(strict_types=1);

namespace App\Domain\Security\Services;

use App\Domain\Auth\DTOs\Impl\ChangePasswordDataDTO;
use App\Domain\Auth\DTOs\Impl\LoginDataDTO;
use App\Application\Shared\DTOs\Impl\ValidationResult;
use Psr\Http\Message\ServerRequestInterface;

interface AuthValidationServiceInterface
{
    public function validateLoginRequest(ServerRequestInterface $request): LoginDataDTO;
    public function validateChangePasswordRequest(ServerRequestInterface $request): ChangePasswordDataDTO;
    public function extractUserIdFromRequest(ServerRequestInterface $request, array $args): int;
    public function validateUserId(int $id): ValidationResult;
}
