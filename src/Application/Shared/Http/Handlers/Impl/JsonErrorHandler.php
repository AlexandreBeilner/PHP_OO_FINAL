<?php

declare(strict_types=1);

namespace App\Application\Shared\Http\Handlers\Impl;

use App\Application\Shared\DTOs\Impl\ApiResponse;
use App\Application\Shared\Http\Handlers\JsonErrorHandlerInterface;
use Exception;
use InvalidArgumentException;
use Nyholm\Psr7\Response;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;
use Throwable;
use TypeError;

/**
 * Handler de erros personalizado para retornar sempre JSON
 * Padroniza todas as mensagens de erro em português do Brasil
 */
final class JsonErrorHandler implements JsonErrorHandlerInterface
{
    private bool $displayErrorDetails;
    private bool $logErrorDetails;
    private bool $logErrors;

    public function __construct(bool $displayErrorDetails = false, bool $logErrors = true, bool $logErrorDetails = true)
    {
        $this->displayErrorDetails = $displayErrorDetails;
        $this->logErrors = $logErrors;
        $this->logErrorDetails = $logErrorDetails;
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        $response = new Response();

        // Determinar código de status e mensagem baseado no tipo de exceção
        $statusCode = 500;
        $message = 'Erro interno do servidor';
        $data = null;

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getCode();
            $message = $this->getHttpExceptionMessage($exception);
        } elseif ($exception instanceof InvalidArgumentException) {
            $statusCode = 400;
            $message = 'Dados inválidos: ' . $exception->getMessage();
        } elseif ($exception instanceof TypeError) {
            $statusCode = 400;
            $message = 'Tipo de dados inválido: ' . $exception->getMessage();
        } elseif ($exception instanceof PDOException) {
            $statusCode = 500;
            $message = 'Erro de banco de dados';
            $data = $this->displayErrorDetails ? ['error' => $exception->getMessage()] : null;
        }

        // Adicionar detalhes do erro se habilitado
        if ($this->displayErrorDetails && $exception instanceof Exception) {
            $data = [
                'error' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        // Criar resposta JSON padronizada
        $apiResponse = new ApiResponse(false, $data, $message, $statusCode);

        $response->getBody()->write($apiResponse->toJson());

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($statusCode);
    }

    private function getHttpExceptionMessage(HttpException $exception): string
    {
        switch (true) {
            case $exception instanceof HttpNotFoundException:
                return 'Recurso não encontrado';
            case $exception instanceof HttpMethodNotAllowedException:
                return 'Método não permitido';
            case $exception instanceof HttpBadRequestException:
                return 'Requisição inválida';
            case $exception instanceof HttpUnauthorizedException:
                return 'Não autorizado';
            case $exception instanceof HttpForbiddenException:
                return 'Acesso negado';
            case $exception instanceof HttpInternalServerErrorException:
                return 'Erro interno do servidor';
            default:
                return $exception->getMessage() ?: 'Erro HTTP';
        }
    }
}
