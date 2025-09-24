<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Helpers;

use App\Application\Shared\DTOs\Impl\ApiResponse;

interface ResponseHelperInterface
{
    public static function error(string $message, int $code = 400, $data = null): ApiResponse;

    public static function forbidden(string $message = 'Forbidden'): ApiResponse;

    public static function notFound(string $message = 'Resource not found'): ApiResponse;

    public static function paginated($data, int $page, int $limit, int $total): ApiResponse;

    public static function serverError(string $message = 'Internal server error'): ApiResponse;

    public static function success($data = null, string $message = 'Success', int $code = 200): ApiResponse;

    public static function unauthorized(string $message = 'Unauthorized'): ApiResponse;

    public static function validationError(array $errors, string $message = 'Validation failed'): ApiResponse;
}
