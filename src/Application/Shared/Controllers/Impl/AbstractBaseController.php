<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Impl;

use App\Application\Shared\Controllers\BaseControllerInterface;
use App\Application\Shared\Controllers\Helpers\Impl\ResponseHelper;
use App\Application\Shared\DTOs\ApiResponseInterface;

abstract class AbstractBaseController implements BaseControllerInterface
{
    public function error(string $message = 'Erro', int $statusCode = 400, $data = null): ApiResponseInterface
    {
        return ResponseHelper::error($message, $statusCode, $data);
    }

    public function forbidden(string $message = 'Proibido'): ApiResponseInterface
    {
        return ResponseHelper::forbidden($message);
    }

    public function notFound(string $message = 'Não Encontrado'): ApiResponseInterface
    {
        return ResponseHelper::notFound($message);
    }

    public function success($data = null, string $message = 'Sucesso', int $statusCode = 200): ApiResponseInterface
    {
        return ResponseHelper::success($data, $message, $statusCode);
    }

    public function unauthorized(string $message = 'Não Autorizado'): ApiResponseInterface
    {
        return ResponseHelper::unauthorized($message);
    }

    public function validationError(array $errors, string $message = 'Erro de Validação'): ApiResponseInterface
    {
        return ResponseHelper::validationError($errors, $message);
    }

    protected function paginated($data, int $page, int $limit, int $total): ApiResponseInterface
    {
        return ResponseHelper::paginated($data, $page, $limit, $total);
    }

    protected function serverError(string $message = 'Erro interno do servidor'): ApiResponseInterface
    {
        return ResponseHelper::serverError($message);
    }
}
