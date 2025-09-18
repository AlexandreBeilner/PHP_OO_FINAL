<?php

declare(strict_types=1);

namespace App\Domain\Security\Services\Impl;

use App\Application\Common\DTOs\Auth\Impl\ChangePasswordRequestDTO;
use App\Application\Common\DTOs\Auth\Impl\LoginRequestDTO;
use App\Application\Common\DTOs\Impl\ValidationResult;
use App\Domain\Common\Exceptions\Impl\ValidationException;
use App\Domain\Security\Services\AuthValidationServiceInterface;
use App\Domain\Security\Validators\AuthDataValidatorInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AuthValidationService implements AuthValidationServiceInterface
{
    private AuthDataValidatorInterface $authDataValidator;

    public function __construct(AuthDataValidatorInterface $authDataValidator)
    {
        $this->authDataValidator = $authDataValidator;
    }

    public function validateLoginRequest(ServerRequestInterface $request): LoginRequestDTO
    {
        $data = $request->getParsedBody();
        
        $validation = $this->authDataValidator->validateLoginData($data);
        if (!$validation->isValid()) {
            throw new ValidationException('Dados de login inválidos', $validation->getErrors());
        }

        return LoginRequestDTO::fromArray($data);
    }

    public function validateChangePasswordRequest(ServerRequestInterface $request): ChangePasswordRequestDTO
    {
        $data = $request->getParsedBody();
        
        $validation = $this->authDataValidator->validateChangePasswordData($data);
        if (!$validation->isValid()) {
            throw new ValidationException('Dados para alteração de senha inválidos', $validation->getErrors());
        }

        return ChangePasswordRequestDTO::fromArray($data);
    }

    public function extractUserIdFromRequest(ServerRequestInterface $request, array $args): int
    {
        $id = (int) ($args['id'] ?? 0);

        // Fallback para extrair ID da URL se não vier nos args
        if ($id === 0) {
            $uri = $request->getUri()->getPath();
            if (preg_match('/\/(\d+)(?:\/|$)/', $uri, $matches)) {
                $id = (int) $matches[1];
            }
        }

        $validation = $this->authDataValidator->validateUserId($id);
        if (!$validation->isValid()) {
            throw new ValidationException('ID inválido', $validation->getErrors());
        }

        return $id;
    }

    public function validateUserId(int $id): ValidationResult
    {
        return $this->authDataValidator->validateUserId($id);
    }
}
