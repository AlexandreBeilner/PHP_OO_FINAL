<?php

declare(strict_types=1);

namespace App\Domain\Security\Services;

use App\Application\Common\DTOs\Auth\Impl\ChangePasswordRequestDTO;
use App\Application\Common\DTOs\Auth\Impl\LoginRequestDTO;
use App\Application\Common\DTOs\Impl\ValidationResult;
use Psr\Http\Message\ServerRequestInterface;

interface AuthValidationServiceInterface
{
    public function validateLoginRequest(ServerRequestInterface $request): LoginRequestDTO;
    public function validateChangePasswordRequest(ServerRequestInterface $request): ChangePasswordRequestDTO;
    public function extractUserIdFromRequest(ServerRequestInterface $request, array $args): int;
    public function validateUserId(int $id): ValidationResult;
}
