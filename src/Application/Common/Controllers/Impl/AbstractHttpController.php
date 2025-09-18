<?php

declare(strict_types=1);

namespace App\Application\Common\Controllers\Impl;

use App\Application\Common\Controllers\HttpControllerInterface;
use App\Application\Common\DTOs\Impl\ApiResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractHttpController implements HttpControllerInterface
{
    protected function errorResponse(ResponseInterface $response, string $message, int $statusCode = 400): ResponseInterface
    {
        $apiResponse = new ApiResponse(false, [], $message, $statusCode);

        $response->getBody()->write(json_encode($apiResponse->toArray()));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }

    protected function getJsonData(ServerRequestInterface $request): array
    {
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);

        return is_array($data) ? $data : [];
    }

    protected function getPathParams(ServerRequestInterface $request, array $args): array
    {
        return $args;
    }

    protected function getQueryParams(ServerRequestInterface $request): array
    {
        return $request->getQueryParams();
    }

    protected function jsonResponse(ResponseInterface $response, array $data, int $statusCode = 200): ResponseInterface
    {
        $apiResponse = new ApiResponse(true, $data, 'Success', $statusCode);

        $response->getBody()->write(json_encode($apiResponse->toArray()));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }

    protected function successResponse(ResponseInterface $response, array $data = [], int $statusCode = 200): ResponseInterface
    {
        $apiResponse = new ApiResponse(true, $data, 'Success', $statusCode);

        $response->getBody()->write(json_encode($apiResponse->toArray()));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}
