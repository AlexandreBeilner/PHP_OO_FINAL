<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud\Impl;

use App\Application\Shared\Controllers\Crud\ExceptionHandlerInterface;
use App\Application\Shared\Controllers\Helpers\Impl\ResponseHelper;
use App\Application\Shared\DTOs\ApiResponseInterface;
use App\Domain\Common\Exceptions\Impl\BusinessLogicExceptionAbstract;
use App\Domain\Common\Exceptions\Impl\ValidationException;
use Exception;

final class StandardExceptionHandler implements ExceptionHandlerInterface
{
    private array $handlers;

    public function __construct()
    {
        $this->handlers = $this->buildDefaultHandlers();
    }

    public function handle(Exception $exception): ApiResponseInterface
    {
        return $this->findHandlerFor($exception);
    }

    public function registerHandler(string $exceptionClass, callable $handler): void
    {
        $this->handlers[$exceptionClass] = $handler;
    }

    private function buildDefaultHandlers(): array
    {
        return [
            ValidationException::class => [$this, 'handleValidationException'],
            BusinessLogicExceptionAbstract::class => [$this, 'handleBusinessLogicException']
        ];
    }

    private function findHandlerFor(Exception $exception): ApiResponseInterface
    {
        $exceptionClass = get_class($exception);
        
        if (isset($this->handlers[$exceptionClass])) {
            return $this->handlers[$exceptionClass]($exception);
        }
        
        return $this->handleGenericException($exception);
    }

    private function handleValidationException(ValidationException $exception): ApiResponseInterface
    {
        return ResponseHelper::validationError($exception->getErrors(), $exception->getMessage());
    }

    private function handleBusinessLogicException(BusinessLogicExceptionAbstract $exception): ApiResponseInterface
    {
        return ResponseHelper::error($exception->getMessage(), 409);
    }

    private function handleGenericException(Exception $exception): ApiResponseInterface
    {
        if ($exception instanceof BusinessLogicExceptionAbstract) {
            return $this->handleBusinessLogicException($exception);
        }
        
        return ResponseHelper::serverError('Erro interno: ' . $exception->getMessage());
    }
}
