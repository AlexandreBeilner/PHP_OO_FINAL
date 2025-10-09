<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Http\Handlers\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Http\Handlers\Impl\JsonErrorHandler;
use App\Application\Shared\Http\Handlers\JsonErrorHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Exception;
use InvalidArgumentException;
use TypeError;
use PDOException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpInternalServerErrorException;

final class JsonErrorHandlerTest extends TestCase
{
    private JsonErrorHandler $errorHandler;
    private ServerRequestInterface $request;

    protected function setUp(): void
    {
        $this->errorHandler = new JsonErrorHandler();
        $this->request = $this->createMock(ServerRequestInterface::class);
    }

    public function testImplementsJsonErrorHandlerInterface(): void
    {
        $this->assertInstanceOf(JsonErrorHandlerInterface::class, $this->errorHandler);
    }

    public function testConstructorWithDefaultParameters(): void
    {
        $handler = new JsonErrorHandler();

        $this->assertInstanceOf(JsonErrorHandler::class, $handler);
        $this->assertInstanceOf(JsonErrorHandlerInterface::class, $handler);
    }

    public function testConstructorWithCustomParameters(): void
    {
        $handler = new JsonErrorHandler(true, false, true);

        $this->assertInstanceOf(JsonErrorHandler::class, $handler);
        $this->assertInstanceOf(JsonErrorHandlerInterface::class, $handler);
    }

    public function testInvokeReturnsResponseInterface(): void
    {
        $exception = new Exception('Test exception');

        $result = ($this->errorHandler)($this->request, $exception, false, false, false);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testInvokeWithGenericExceptionReturns500(): void
    {
        $exception = new Exception('Generic error');

        $response = ($this->errorHandler)($this->request, $exception, false, false, false);

        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testInvokeWithHttpNotFoundException(): void
    {
        $exception = new HttpNotFoundException($this->request, 'Not found');

        $response = ($this->errorHandler)($this->request, $exception, false, false, false);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testInvokeWithHttpBadRequestException(): void
    {
        $exception = new HttpBadRequestException($this->request, 'Bad request');

        $response = ($this->errorHandler)($this->request, $exception, false, false, false);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testInvokeWithHttpUnauthorizedException(): void
    {
        $exception = new HttpUnauthorizedException($this->request, 'Unauthorized');

        $response = ($this->errorHandler)($this->request, $exception, false, false, false);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testInvokeWithHttpForbiddenException(): void
    {
        $exception = new HttpForbiddenException($this->request, 'Forbidden');

        $response = ($this->errorHandler)($this->request, $exception, false, false, false);

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testInvokeWithInvalidArgumentException(): void
    {
        $exception = new InvalidArgumentException('Invalid data');

        $response = ($this->errorHandler)($this->request, $exception, false, false, false);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testInvokeWithTypeError(): void
    {
        $exception = new TypeError('Type error');

        $response = ($this->errorHandler)($this->request, $exception, false, false, false);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testInvokeWithPDOException(): void
    {
        $exception = new PDOException('Database error');

        $response = ($this->errorHandler)($this->request, $exception, false, false, false);

        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testInvokeReturnsJsonContentType(): void
    {
        $exception = new Exception('Test error');

        $response = ($this->errorHandler)($this->request, $exception, false, false, false);

        $contentType = $response->getHeaderLine('Content-Type');
        $this->assertEquals('application/json; charset=utf-8', $contentType);
    }

    public function testInvokeResponseBodyIsValidJson(): void
    {
        $exception = new Exception('Test error');

        $response = ($this->errorHandler)($this->request, $exception, false, false, false);
        
        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);

        $this->assertNotNull($decoded);
        $this->assertIsArray($decoded);
    }

    public function testInvokeResponseStructure(): void
    {
        $exception = new Exception('Test error');

        $response = ($this->errorHandler)($this->request, $exception, false, false, false);
        
        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);

        $this->assertArrayHasKey('success', $decoded);
        $this->assertArrayHasKey('message', $decoded);
        $this->assertArrayHasKey('code', $decoded);
        $this->assertFalse($decoded['success']);
    }

    public function testInvokeWithDisplayErrorDetailsDisabled(): void
    {
        $handler = new JsonErrorHandler(false);
        $exception = new Exception('Detailed error message');

        $response = ($handler)($this->request, $exception, false, false, false);
        
        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);

        // Should not include error details
        $this->assertArrayNotHasKey('error', $decoded['data'] ?? []);
    }

    public function testInvokeWithDisplayErrorDetailsEnabled(): void
    {
        $handler = new JsonErrorHandler(true);
        $exception = new Exception('Detailed error message');

        $response = ($handler)($this->request, $exception, true, false, false);
        
        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);

        // Should include error details
        $this->assertIsArray($decoded['data']);
        $this->assertArrayHasKey('error', $decoded['data']);
        $this->assertEquals('Detailed error message', $decoded['data']['error']);
    }

    public function testInvokeWithPDOExceptionAndErrorDetails(): void
    {
        $handler = new JsonErrorHandler(true);
        $exception = new PDOException('Connection failed');

        $response = ($handler)($this->request, $exception, true, false, false);
        
        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertIsArray($decoded['data']);
        $this->assertArrayHasKey('error', $decoded['data']);
    }

    public function testInvokeWithDifferentHttpExceptions(): void
    {
        $exceptions = [
            [new HttpNotFoundException($this->request, 'Not found'), 404, 'Recurso não encontrado'],
            [new HttpMethodNotAllowedException($this->request, 'Not allowed'), 405, 'Método não permitido'],
            [new HttpBadRequestException($this->request, 'Bad request'), 400, 'Requisição inválida'],
            [new HttpUnauthorizedException($this->request, 'Unauthorized'), 401, 'Não autorizado'],
            [new HttpForbiddenException($this->request, 'Forbidden'), 403, 'Acesso negado'],
            [new HttpInternalServerErrorException($this->request, 'Server error'), 500, 'Erro interno do servidor']
        ];

        foreach ($exceptions as $testCase) {
            [$exception, $expectedCode, $expectedMessage] = $testCase;
            
            $response = ($this->errorHandler)($this->request, $exception, false, false, false);
            
            $this->assertEquals($expectedCode, $response->getStatusCode());
            
            $body = (string) $response->getBody();
            $decoded = json_decode($body, true);
            $this->assertEquals($expectedMessage, $decoded['message']);
        }
    }

    public function testHandlerIsCallable(): void
    {
        $this->assertTrue(is_callable($this->errorHandler));
    }

    public function testMultipleInvocationsAreIndependent(): void
    {
        $exception1 = new Exception('First error');
        $exception2 = new InvalidArgumentException('Second error');

        $response1 = ($this->errorHandler)($this->request, $exception1, false, false, false);
        $response2 = ($this->errorHandler)($this->request, $exception2, false, false, false);

        $this->assertNotSame($response1, $response2);
        $this->assertEquals(500, $response1->getStatusCode());
        $this->assertEquals(400, $response2->getStatusCode());
    }

    public function testConstructorParametersAreStored(): void
    {
        // We can't directly test private properties, but we can test behavior
        $handlerWithDetails = new JsonErrorHandler(true);
        $handlerWithoutDetails = new JsonErrorHandler(false);

        $exception = new Exception('Test error');

        $responseWithDetails = ($handlerWithDetails)($this->request, $exception, true, false, false);
        $responseWithoutDetails = ($handlerWithoutDetails)($this->request, $exception, false, false, false);

        $bodyWith = (string) $responseWithDetails->getBody();
        $bodyWithout = (string) $responseWithoutDetails->getBody();

        // The responses should be different based on constructor parameters
        $this->assertNotEquals($bodyWith, $bodyWithout);
    }
}
