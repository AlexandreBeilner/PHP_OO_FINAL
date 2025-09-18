<?php

declare(strict_types=1);

namespace App\Domain\Security\Services\Impl;

use App\Application\Common\DTOs\User\Impl\CreateUserRequestDTO;
use App\Application\Common\DTOs\User\Impl\UpdateUserRequestDTO;
use App\Application\Common\DTOs\Impl\ValidationResult;
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

    public function validateCreateUserRequest(ServerRequestInterface $request): CreateUserRequestDTO
    {
        $data = $request->getParsedBody();
        
        $validation = $this->userDataValidator->validateCreateUserData($data);
        if (!$validation->isValid()) {
            throw new ValidationException('Dados para criação de usuário inválidos', $validation->getErrors());
        }

        return CreateUserRequestDTO::fromArray($data);
    }

    public function validateUpdateUserRequest(ServerRequestInterface $request): UpdateUserRequestDTO
    {
        $data = $request->getParsedBody();
        
        $validation = $this->userDataValidator->validateUpdateUserData($data);
        if (!$validation->isValid()) {
            throw new ValidationException('Dados para atualização de usuário inválidos', $validation->getErrors());
        }

        return UpdateUserRequestDTO::fromArray($data);
    }

    public function validateUserId(int $id): ValidationResult
    {
        return $this->userDataValidator->validateUserId($id);
    }
}
