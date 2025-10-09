<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Helpers\Impl;

use App\Application\Shared\Controllers\Helpers\ResponseHelperInterface;
use App\Application\Shared\DTOs\Impl\ApiResponse;

final class ResponseHelper implements ResponseHelperInterface
{
    public static function error(string $message, int $code = 400, $data = null): ApiResponse
    {
        return new ApiResponse(false, $data, $message, $code);
    }

    public static function forbidden(string $message = 'Forbidden'): ApiResponse
    {
        return new ApiResponse(false, null, $message, 403);
    }

    public static function notFound(string $message = 'Resource not found'): ApiResponse
    {
        return new ApiResponse(false, null, $message, 404);
    }

    public static function paginated($data, int $page, int $limit, int $total): ApiResponse
    {
        $pagination = [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit),
        ];

        return new ApiResponse(true, [
            'data' => $data,
            'pagination' => $pagination,
        ], 'Success', 200);
    }

    public static function serverError(string $message = 'Internal server error'): ApiResponse
    {
        return new ApiResponse(false, null, $message, 500);
    }

    public static function success($data = null, string $message = 'Success', int $code = 200): ApiResponse
    {
        return new ApiResponse(true, $data, $message, $code);
    }

    public static function unauthorized(string $message = 'Unauthorized'): ApiResponse
    {
        return new ApiResponse(false, null, $message, 401);
    }

    public static function validationError(array $errors, string $message = 'Validation failed'): ApiResponse
    {
        return new ApiResponse(false, ['errors' => $errors], $message, 422);
    }
}
