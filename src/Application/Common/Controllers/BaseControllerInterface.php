<?php

declare(strict_types=1);

namespace App\Application\Common\Controllers;

use App\Application\Common\DTOs\ApiResponseInterface;

interface BaseControllerInterface
{
    public function error(string $message = 'Error', int $statusCode = 400, $data = null): ApiResponseInterface;

    public function forbidden(string $message = 'Forbidden'): ApiResponseInterface;

    public function notFound(string $message = 'Not Found'): ApiResponseInterface;

    public function success($data = null, string $message = 'Success', int $statusCode = 200): ApiResponseInterface;

    public function unauthorized(string $message = 'Unauthorized'): ApiResponseInterface;

    public function validationError(array $errors, string $message = 'Validation Error'): ApiResponseInterface;
}
