<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud\Impl;

use App\Application\Shared\Controllers\Crud\CrudOperationInterface;
use App\Application\Shared\Controllers\Crud\CrudResultInterface;
use App\Application\Shared\Controllers\Crud\ExceptionHandlerInterface;
use App\Application\Shared\Controllers\Crud\HttpRequestHandlerInterface;
use App\Application\Shared\DTOs\ApiResponseInterface;
use App\Application\Shared\DTOs\Impl\ApiResponse;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class StandardHttpRequestHandler implements HttpRequestHandlerInterface
{
    private ExceptionHandlerInterface $exceptionHandler;

    public function __construct(ExceptionHandlerInterface $exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;
    }

    public function handle(
        ServerRequestInterface $request, 
        ResponseInterface $response, 
        CrudOperationInterface $operation,
        array $pathParams = []
    ): ResponseInterface {
        return $this->executeOperation($request, $response, $operation, $pathParams);
    }

    private function executeOperation(
        ServerRequestInterface $request,
        ResponseInterface $response,
        CrudOperationInterface $operation,
        array $pathParams
    ): ResponseInterface {
        try {
            $result = $operation->execute($request, $pathParams);
            return $this->buildSuccessResponse($response, $result);
        } catch (Exception $exception) {
            return $this->buildExceptionResponse($response, $exception);
        }
    }

    private function buildSuccessResponse(ResponseInterface $response, CrudResultInterface $result): ResponseInterface
    {
        $apiResponse = $this->createSuccessApiResponse($result);
        return $this->writeJsonResponse($response, $apiResponse, $result->getCode());
    }

    private function buildExceptionResponse(ResponseInterface $response, Exception $exception): ResponseInterface
    {
        $apiResponse = $this->exceptionHandler->handle($exception);
        return $this->writeJsonResponse($response, $apiResponse, $apiResponse->getCode());
    }

    private function createSuccessApiResponse(CrudResultInterface $result): ApiResponse
    {
        $meta = $this->extractMetaFrom($result);
        
        return new ApiResponse(
            true, 
            $result->getData(), 
            $result->getMessage(), 
            $result->getCode(), 
            $meta
        );
    }

    private function extractMetaFrom(CrudResultInterface $result): array
    {
        return $result->hasMeta() ? $result->getMeta() : [];
    }

    private function writeJsonResponse(
        ResponseInterface $response, 
        ApiResponseInterface $apiResponse, 
        int $statusCode
    ): ResponseInterface {
        $response->getBody()->write($apiResponse->toJson());
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}
