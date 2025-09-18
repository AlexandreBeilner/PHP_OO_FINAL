<?php

declare(strict_types=1);

namespace App\Application\Modules\Auth\Controllers\Impl;

use App\Application\Common\Controllers\Impl\AbstractBaseController;
use App\Application\Modules\Auth\Controllers\AuthControllerInterface;
use App\Domain\Security\Services\AuthServiceInterface;
use App\Domain\Security\Services\UserServiceInterface;
use App\Domain\Security\Services\AuthValidationServiceInterface;
use App\Domain\Common\Exceptions\Impl\ValidationException;
use App\Domain\Common\Exceptions\Impl\BusinessLogicExceptionAbstract;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AuthController extends AbstractBaseController implements AuthControllerInterface
{
    private AuthServiceInterface $authService;
    private UserServiceInterface $userService;
    private AuthValidationServiceInterface $authValidationService;

    public function __construct(
        AuthServiceInterface $authService, 
        UserServiceInterface $userService,
        AuthValidationServiceInterface $authValidationService
    ) {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->authValidationService = $authValidationService;
    }

    public function activateUser(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $request->getAttribute('id'));
            
            $user = $this->userService->getUserById($id);
            if (! $user) {
                $apiResponse = $this->notFound('Usuário não encontrado');
                $response->getBody()->write($apiResponse->toJson());
                return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
            }

            $updatedUser = $this->userService->updateUser($id, ['status' => 'active']);

            $apiResponse = $this->success($updatedUser, 'Usuário ativado com sucesso');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (Exception $e) {
            $apiResponse = $this->serverError('Erro ao ativar usuário: ' . $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }

    public function authenticate(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $loginRequest = $this->authValidationService->validateLoginRequest($request);
            $authResult = $this->authService->authenticate($loginRequest->email, $loginRequest->password);

            if (! $authResult['success']) {
                $apiResponse = $this->unauthorized($authResult['message']);
                $response->getBody()->write($apiResponse->toJson());
                return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
            }

            $apiResponse = $this->success($authResult, 'Autenticação realizada com sucesso');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (ValidationException $e) {
            $apiResponse = $this->validationError($e->getErrors(), $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (Exception $e) {
            $apiResponse = $this->serverError('Erro na autenticação: ' . $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }

    public function changePassword(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $changePasswordRequest = $this->authValidationService->validateChangePasswordRequest($request);
            $user = $this->userService->changePassword(
                $changePasswordRequest->getUserId(),
                $changePasswordRequest->getNewPassword()
            );

            if (! $user) {
                $apiResponse = $this->notFound('Usuário não encontrado');
                $response->getBody()->write($apiResponse->toJson());
                return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
            }

            $apiResponse = $this->success($user, 'Senha alterada com sucesso');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (ValidationException $e) {
            $apiResponse = $this->validationError($e->getErrors(), $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (BusinessLogicExceptionAbstract $e) {
            $apiResponse = $this->error($e->getMessage(), 409);
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (Exception $e) {
            $apiResponse = $this->serverError('Erro ao alterar senha: ' . $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }

    public function deactivateUser(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $request->getAttribute('id'));
            
            $user = $this->userService->getUserById($id);
            if (! $user) {
                $apiResponse = $this->notFound('Usuário não encontrado');
                $response->getBody()->write($apiResponse->toJson());
                return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
            }

            $updatedUser = $this->userService->updateUser($id, ['status' => 'inactive']);

            $apiResponse = $this->success($updatedUser, 'Usuário desativado com sucesso');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (Exception $e) {
            $apiResponse = $this->serverError('Erro ao desativar usuário: ' . $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $loginRequest = $this->authValidationService->validateLoginRequest($request);
            $user = $this->userService->authenticateUser($loginRequest->getEmail(), $loginRequest->getPassword());

            if (! $user) {
                $apiResponse = $this->unauthorized('Credenciais inválidas');
                $response->getBody()->write($apiResponse->toJson());
                return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
            }

            $apiResponse = $this->success($user, 'Login realizado com sucesso');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (ValidationException $e) {
            $apiResponse = $this->validationError($e->getErrors(), $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (Exception $e) {
            $apiResponse = $this->serverError('Erro ao realizar login: ' . $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }
}
