<?php

declare(strict_types=1);

namespace App\Domain\Security\Services\Impl;

use App\Application\Shared\DTOs\Impl\ValidationResult;
use App\Domain\Auth\Commands\Impl\ChangePasswordCommand;
use App\Domain\Auth\Commands\Impl\LoginCommand;
use App\Domain\Auth\DTOs\Impl\ChangePasswordDataDTO;
use App\Domain\Auth\DTOs\Impl\LoginDataDTO;
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
        if (! $validation->isValid()) {
            throw new ValidationException('ID inválido', $validation->getErrors());
        }

        return $id;
    }

    public function validateChangePasswordCommand(ServerRequestInterface $request): ChangePasswordCommand
    {
        $data = $request->getParsedBody();

        $validation = $this->authDataValidator->validateChangePasswordData($data);
        if (! $validation->isValid()) {
            throw new ValidationException('Dados para alteração de senha inválidos', $validation->getErrors());
        }

        return ChangePasswordCommand::fromArray($data);
    }

    public function validateChangePasswordRequest(ServerRequestInterface $request): ChangePasswordDataDTO
    {
        $data = $request->getParsedBody();

        $validation = $this->authDataValidator->validateChangePasswordData($data);
        if (! $validation->isValid()) {
            throw new ValidationException('Dados para alteração de senha inválidos', $validation->getErrors());
        }

        return ChangePasswordDataDTO::fromArray($data);
    }

    public function validateLoginCommand(ServerRequestInterface $request): LoginCommand
    {
        $data = $request->getParsedBody();

        $validation = $this->authDataValidator->validateLoginData($data);
        if (! $validation->isValid()) {
            throw new ValidationException('Dados de login inválidos', $validation->getErrors());
        }

        return LoginCommand::fromArray($data);
    }

    // ✅ COMMAND PATTERN: Retorna LoginCommand ao invés de DTO

    public function validateLoginRequest(ServerRequestInterface $request): LoginDataDTO
    {
        $data = $request->getParsedBody();

        $validation = $this->authDataValidator->validateLoginData($data);
        if (! $validation->isValid()) {
            throw new ValidationException('Dados de login inválidos', $validation->getErrors());
        }

        return LoginDataDTO::fromArray($data);
    }

    // ✅ COMMAND PATTERN: Retorna ChangePasswordCommand ao invés de DTO

    public function validateUserId(int $id): ValidationResult
    {
        return $this->authDataValidator->validateUserId($id);
    }
}
