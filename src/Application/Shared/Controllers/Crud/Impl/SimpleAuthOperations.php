<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud\Impl;

use App\Application\Shared\Controllers\Crud\CrudOperationInterface;
use App\Application\Shared\Controllers\Crud\CrudResultInterface;
use App\Domain\Security\Services\AuthServiceInterface;
use App\Domain\Security\Services\AuthValidationServiceInterface;
use App\Domain\Security\Services\UserServiceInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SimpleAuthOperations
{
    private UserServiceInterface $userService;
    private AuthServiceInterface $authService;
    private AuthValidationServiceInterface $authValidationService;

    public function __construct(
        UserServiceInterface $userService,
        AuthServiceInterface $authService,
        AuthValidationServiceInterface $authValidationService
    ) {
        $this->userService = $userService;
        $this->authService = $authService;
        $this->authValidationService = $authValidationService;
    }

    public function createLoginOperation(): CrudOperationInterface
    {
        return new class($this->authValidationService, $this->userService) implements CrudOperationInterface {
            private AuthValidationServiceInterface $validator;
            private UserServiceInterface $userService;

            public function __construct(AuthValidationServiceInterface $validator, UserServiceInterface $userService)
            {
                $this->validator = $validator;
                $this->userService = $userService;
            }

            public function execute(ServerRequestInterface $request, array $pathParams = []): CrudResultInterface
            {
                $loginData = $this->validator->validateLoginRequest($request);
                $user = $this->userService->authenticateUser($loginData->email, $loginData->password);
                
                if (!$user) {
                    throw new \App\Domain\Common\Exceptions\Impl\BusinessLogicExceptionAbstract('Credenciais inválidas', 401);
                }
                
                return new CrudResult($user, 'Login realizado com sucesso', 200);
            }
        };
    }

    public function createChangePasswordOperation(): CrudOperationInterface
    {
        return new class($this->authValidationService, $this->authService) implements CrudOperationInterface {
            private AuthValidationServiceInterface $validator;
            private AuthServiceInterface $authService;

            public function __construct(AuthValidationServiceInterface $validator, AuthServiceInterface $authService)
            {
                $this->validator = $validator;
                $this->authService = $authService;
            }

            public function execute(ServerRequestInterface $request, array $pathParams = []): CrudResultInterface
            {
                $command = $this->validator->validateChangePasswordCommand($request);
                $result = $command->executeWith($this->authService);
                
                if (!$result) {
                    throw new \App\Domain\Common\Exceptions\Impl\BusinessLogicExceptionAbstract('Erro ao alterar senha', 400);
                }
                
                return new CrudResult($result, 'Senha alterada com sucesso', 200);
            }
        };
    }
}
