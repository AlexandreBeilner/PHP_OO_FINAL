<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\Factories\Impl;

use App\Application\Shared\Controllers\Crud\RequestValidatorInterface;
use App\Domain\Security\Services\UserValidationServiceInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UserRequestValidator implements RequestValidatorInterface
{
    private UserValidationServiceInterface $userValidationService;

    public function __construct(UserValidationServiceInterface $userValidationService)
    {
        $this->userValidationService = $userValidationService;
    }

    public function validateCreateCommand(ServerRequestInterface $request)
    {
        return $this->userValidationService->validateCreateUserCommand($request);
    }

    public function validateUpdateCommand(ServerRequestInterface $request)
    {
        return $this->userValidationService->validateUpdateUserCommand($request);
    }
}
