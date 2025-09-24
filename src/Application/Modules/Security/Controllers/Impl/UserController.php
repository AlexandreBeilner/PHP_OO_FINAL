<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\Controllers\Impl;

use App\Application\Shared\Controllers\Impl\AbstractBaseController;
use App\Application\Modules\Security\Controllers\UserControllerInterface;
use App\Domain\Common\Exceptions\Impl\BusinessLogicExceptionAbstract;
use App\Domain\Common\Exceptions\Impl\ValidationException;
use App\Domain\Security\Services\UserServiceInterface;
use App\Domain\Security\Services\UserValidationServiceInterface;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UserController extends AbstractBaseController implements UserControllerInterface
{
    private UserServiceInterface $userService;
    private UserValidationServiceInterface $userValidationService;

    public function __construct(
        UserServiceInterface $userService,
        UserValidationServiceInterface $userValidationService
    ) {
        $this->userService = $userService;
        $this->userValidationService = $userValidationService;
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $createUserCommand = $this->userValidationService->validateCreateUserCommand($request);
            $user = $createUserCommand->executeWith($this->userService);

            $apiResponse = $this->success($user, 'Usuário criado com sucesso', 201);
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
            $apiResponse = $this->serverError('Erro ao criar usuário: ' . $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $request->getAttribute('id'));
            $deleted = $this->userService->deleteUser($id);

            if (! $deleted) {
                $apiResponse = $this->notFound('Usuário não encontrado');
                $response->getBody()->write($apiResponse->toJson());
                return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
            }

            $apiResponse = $this->success(null, 'Usuário deletado com sucesso');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (Exception $e) {
            $apiResponse = $this->serverError('Erro ao deletar usuário: ' . $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $users = $this->userService->processAllUsers(fn($user) => $user);
            $apiResponse = $this->success($users, 'Usuários listados com sucesso');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (Exception $e) {
            $apiResponse = $this->serverError('Erro ao listar usuários: ' . $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $request->getAttribute('id'));
            $user = $this->userService->processUserById($id, fn($user) => $user);

            $apiResponse = $this->success($user, 'Usuário encontrado com sucesso');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (BusinessLogicExceptionAbstract $e) {
            $apiResponse = $this->notFound('Usuário não encontrado');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (Exception $e) {
            $apiResponse = $this->serverError('Erro ao buscar usuário: ' . $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $request->getAttribute('id'));
            $updateUserCommand = $this->userValidationService->validateUpdateUserCommand($request);
            $user = $updateUserCommand->executeWithUserId($this->userService, $id);

            if (! $user) {
                $apiResponse = $this->notFound('Usuário não encontrado');
                $response->getBody()->write($apiResponse->toJson());
                return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
            }

            $apiResponse = $this->success($user, 'Usuário atualizado com sucesso');
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
            $apiResponse = $this->serverError('Erro ao atualizar usuário: ' . $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }
}
