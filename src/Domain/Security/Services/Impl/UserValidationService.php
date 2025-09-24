<?php

declare(strict_types=1);

namespace App\Domain\Security\Services\Impl;

use App\Domain\Security\DTOs\Impl\CreateUserDTO;
use App\Domain\Security\Commands\Impl\CreateUserCommand;
use App\Domain\Security\DTOs\Impl\UpdateUserDTO;
use App\Domain\Security\Commands\Impl\UpdateUserCommand;
use App\Application\Shared\DTOs\Impl\ValidationResult;
use App\Domain\Common\Exceptions\Impl\ValidationException;
use App\Domain\Security\Services\UserValidationServiceInterface;
use App\Domain\Security\Validators\UserDataValidatorInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UserValidationService implements UserValidationServiceInterface
{
    private UserDataValidatorInterface $userDataValidator;

    public function __construct(UserDataValidatorInterface $userDataValidator)
    {
        $this->userDataValidator = $userDataValidator;
    }

    public function validateCreateUserRequest(ServerRequestInterface $request): CreateUserDTO
    {
        $data = $request->getParsedBody();
        
        $validation = $this->userDataValidator->validateCreateUserData($data);
        if (!$validation->isValid()) {
            throw new ValidationException('Dados para criação de usuário inválidos', $validation->getErrors());
        }

        return CreateUserDTO::fromArray($data);
    }

    // ✅ COMMAND PATTERN: Retorna Command ao invés de DTO
    public function validateCreateUserCommand(ServerRequestInterface $request): CreateUserCommand
    {
        $data = $request->getParsedBody();
        
        $validation = $this->userDataValidator->validateCreateUserData($data);
        if (!$validation->isValid()) {
            throw new ValidationException('Dados para criação de usuário inválidos', $validation->getErrors());
        }

        return CreateUserCommand::fromArray($data);
    }

    public function validateUpdateUserRequest(ServerRequestInterface $request): UpdateUserDTO
    {
        $data = $request->getParsedBody();
        
        $validation = $this->userDataValidator->validateUpdateUserData($data);
        if (!$validation->isValid()) {
            throw new ValidationException('Dados para atualização de usuário inválidos', $validation->getErrors());
        }

        return UpdateUserDTO::fromArray($data);
    }

    // ✅ COMMAND PATTERN: Retorna UpdateUserCommand ao invés de DTO
    public function validateUpdateUserCommand(ServerRequestInterface $request): UpdateUserCommand
    {
        $data = $request->getParsedBody();
        
        $validation = $this->userDataValidator->validateUpdateUserData($data);
        if (!$validation->isValid()) {
            throw new ValidationException('Dados para atualização de usuário inválidos', $validation->getErrors());
        }

        return UpdateUserCommand::fromArray($data);
    }

    public function validateUserId(int $id): ValidationResult
    {
        return $this->userDataValidator->validateUserId($id);
    }
}
