<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud\Impl;

use App\Application\Shared\Controllers\Impl\AbstractBaseController;
use App\Application\Modules\Auth\Controllers\AuthControllerInterface;
use App\Domain\Security\Services\UserServiceInterface;
use App\Application\Shared\Controllers\Crud\HttpRequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SimpleAuthController extends AbstractBaseController implements AuthControllerInterface
{
    private HttpRequestHandlerInterface $requestHandler;
    private UserServiceInterface $userService;
    private SimpleAuthOperations $authOperations;

    public function __construct(
        HttpRequestHandlerInterface $requestHandler,
        UserServiceInterface $userService,
        SimpleAuthOperations $authOperations
    ) {
        $this->requestHandler = $requestHandler;
        $this->userService = $userService;
        $this->authOperations = $authOperations;
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $operation = $this->authOperations->createLoginOperation();
        return $this->requestHandler->handle($request, $response, $operation);
    }

    public function changePassword(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $operation = $this->authOperations->createChangePasswordOperation();
        return $this->requestHandler->handle($request, $response, $operation);
    }

    public function activateUser(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $request->getAttribute('id'));
            
            if ($id <= 0) {
                $apiResponse = $this->error('ID inválido', 400);
                $response->getBody()->write($apiResponse->toJson());
                return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
            }
            
            // Direct call to UserService - bypassing the factory
            $user = $this->userService->activateUser($id);
            
            $apiResponse = $this->success($user, 'Usuário ativado com sucesso');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
            
        } catch (\Exception $e) {
            $apiResponse = $this->error('Erro ao ativar usuário: ' . $e->getMessage(), 400);
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }

    public function deactivateUser(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $request->getAttribute('id'));
            
            if ($id <= 0) {
                $apiResponse = $this->error('ID inválido', 400);
                $response->getBody()->write($apiResponse->toJson());
                return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
            }
            
            // Direct call to UserService - bypassing the factory
            $user = $this->userService->deactivateUser($id);
            
            $apiResponse = $this->success($user, 'Usuário desativado com sucesso');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
            
        } catch (\Exception $e) {
            $apiResponse = $this->error('Erro ao desativar usuário: ' . $e->getMessage(), 400);
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }
}
