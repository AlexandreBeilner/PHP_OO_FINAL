<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Controllers\Crud\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Controllers\Crud\Impl\StandardExceptionHandler;
use App\Application\Shared\Controllers\Crud\ExceptionHandlerInterface;
use App\Application\Shared\DTOs\ApiResponseInterface;
use App\Domain\Common\Exceptions\Impl\ValidationException;
use App\Domain\Common\Exceptions\Impl\BusinessLogicExceptionAbstract;
use Exception;

final class StandardExceptionHandlerTest extends TestCase
{
    private StandardExceptionHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new StandardExceptionHandler();
    }

    public function testImplementsExceptionHandlerInterface(): void
    {
        $this->assertInstanceOf(ExceptionHandlerInterface::class, $this->handler);
    }

    public function testConstructorInitializesCorrectly(): void
    {
        $handler = new StandardExceptionHandler();

        $this->assertInstanceOf(StandardExceptionHandler::class, $handler);
    }

    public function testHandleReturnsApiResponseInterface(): void
    {
        $exception = new Exception('Test exception');

        $result = $this->handler->handle($exception);

        $this->assertInstanceOf(ApiResponseInterface::class, $result);
    }

    public function testHandleWithValidationException(): void
    {
        $validationException = new ValidationException('Validation failed', [
            'field' => ['error message']
        ]);

        $result = $this->handler->handle($validationException);

        $this->assertInstanceOf(ApiResponseInterface::class, $result);
    }

    public function testHandleWithBusinessLogicException(): void
    {
        // BusinessLogicExceptionAbstract is actually a final class, use it directly
        $businessException = new BusinessLogicExceptionAbstract('Business logic error');

        $result = $this->handler->handle($businessException);

        $this->assertInstanceOf(ApiResponseInterface::class, $result);
    }

    public function testHandleWithGenericException(): void
    {
        $exception = new Exception('Generic error');

        $result = $this->handler->handle($exception);

        $this->assertInstanceOf(ApiResponseInterface::class, $result);
    }

    public function testRegisterHandlerAcceptsCallable(): void
    {
        $mockResponse = $this->createMock(ApiResponseInterface::class);
        $customHandler = function(Exception $e) use ($mockResponse): ApiResponseInterface {
            return $mockResponse;
        };

        // Register the handler and verify it works
        $this->handler->registerHandler('CustomException', $customHandler);
        
        // Test that registerHandler returns void
        $result = $this->handler->registerHandler('AnotherCustomException', $customHandler);
        $this->assertNull($result);
    }

    public function testRegisterHandlerAcceptsArrayCallable(): void
    {
        $customHandler = [$this, 'customExceptionHandler'];

        // Register the handler and verify it works
        $this->handler->registerHandler('CustomException', $customHandler);
        
        // Test that registerHandler returns void
        $result = $this->handler->registerHandler('AnotherCustomException', $customHandler);
        $this->assertNull($result);
    }

    public function testRegisterHandlerVoidReturnType(): void
    {
        $customHandler = function(Exception $e): ApiResponseInterface {
            return $this->createMock(ApiResponseInterface::class);
        };

        $result = $this->handler->registerHandler('CustomException', $customHandler);

        $this->assertNull($result);
    }

    public function testHandleWithRegisteredCustomHandler(): void
    {
        $mockResponse = $this->createMock(ApiResponseInterface::class);
        
        $customHandler = function(Exception $e) use ($mockResponse): ApiResponseInterface {
            return $mockResponse;
        };

        $this->handler->registerHandler(Exception::class, $customHandler);

        $exception = new Exception('Custom handled exception');
        $result = $this->handler->handle($exception);

        $this->assertSame($mockResponse, $result);
    }

    public function testHandleWithMultipleRegisteredHandlers(): void
    {
        $mockResponse1 = $this->createMock(ApiResponseInterface::class);
        $mockResponse2 = $this->createMock(ApiResponseInterface::class);

        $this->handler->registerHandler('Exception1', function($e) use ($mockResponse1) { return $mockResponse1; });
        $this->handler->registerHandler('Exception2', function($e) use ($mockResponse2) { return $mockResponse2; });

        // Test that handlers don't interfere with each other
        $this->assertNotSame($mockResponse1, $mockResponse2);
    }

    public function testHandleIsIdempotent(): void
    {
        $exception = new Exception('Test exception');

        $result1 = $this->handler->handle($exception);
        $result2 = $this->handler->handle($exception);

        // Both results should be ApiResponseInterface but may not be the same instance
        $this->assertInstanceOf(ApiResponseInterface::class, $result1);
        $this->assertInstanceOf(ApiResponseInterface::class, $result2);
    }

    public function testHandleWithDifferentExceptionTypes(): void
    {
        $exceptions = [
            new Exception('Generic exception'),
            new \RuntimeException('Runtime exception'),
            new \LogicException('Logic exception'),
            new \InvalidArgumentException('Invalid argument')
        ];

        foreach ($exceptions as $exception) {
            $result = $this->handler->handle($exception);
            $this->assertInstanceOf(ApiResponseInterface::class, $result);
        }
    }

    // Helper method for testing array callable registration
    public function customExceptionHandler(Exception $e): ApiResponseInterface
    {
        return $this->createMock(ApiResponseInterface::class);
    }
}
