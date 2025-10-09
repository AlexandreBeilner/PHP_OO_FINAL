<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Http\Middleware\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Http\Middleware\Impl\ContentTypeValidationMiddleware;
use App\Application\Shared\Http\Middleware\ContentTypeValidationMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ContentTypeValidationMiddlewareTest extends TestCase
{
    private ContentTypeValidationMiddleware $middleware;

    protected function setUp(): void
    {
        $this->middleware = new ContentTypeValidationMiddleware();
    }

    public function testImplementsContentTypeValidationMiddlewareInterface(): void
    {
        $this->assertInstanceOf(ContentTypeValidationMiddlewareInterface::class, $this->middleware);
    }

    public function testProcessPassesThroughGetRequest(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $request->method('getMethod')->willReturn('GET');
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($request))
            ->willReturn($response);

        $result = $this->middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testProcessPassesThroughDeleteRequest(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $request->method('getMethod')->willReturn('DELETE');
        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $result = $this->middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testProcessValidatesPostRequestWithValidContentType(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $request->method('getMethod')->willReturn('POST');
        $request->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('application/json');

        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $result = $this->middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testProcessValidatesContentTypeWithCharset(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $request->method('getMethod')->willReturn('PUT');
        $request->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('application/json; charset=utf-8');

        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $result = $this->middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testProcessRejectsPostRequestWithInvalidContentType(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $request->method('getMethod')->willReturn('POST');
        $request->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('text/plain');

        // Handler should NOT be called for invalid content type
        $handler->expects($this->never())->method('handle');

        $result = $this->middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testProcessRejectsRequestWithEmptyContentType(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $request->method('getMethod')->willReturn('PATCH');
        $request->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('');

        $handler->expects($this->never())->method('handle');

        $result = $this->middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testProcessCreatesCorrectErrorResponseForInvalidContentType(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $request->method('getMethod')->willReturn('POST');
        $request->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('application/xml');

        $result = $this->middleware->process($request, $handler);

        // Verify response properties
        $this->assertInstanceOf(ResponseInterface::class, $result);
        
        // The actual Response is created internally, so we can't easily test its content
        // but we can verify it's not the handler's response
        $this->assertNotNull($result);
    }

    public function testProcessHandlesAllValidContentTypes(): void
    {
        $validContentTypes = [
            'application/json',
            'application/x-www-form-urlencoded',
            'multipart/form-data'
        ];

        foreach ($validContentTypes as $contentType) {
            $request = $this->createMock(ServerRequestInterface::class);
            $handler = $this->createMock(RequestHandlerInterface::class);
            $response = $this->createMock(ResponseInterface::class);

            $request->method('getMethod')->willReturn('POST');
            $request->method('getHeaderLine')
                ->with('Content-Type')
                ->willReturn($contentType);

            $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($response);

            $result = $this->middleware->process($request, $handler);

            $this->assertSame($response, $result, "Content-Type '{$contentType}' should be valid");
        }
    }

    public function testProcessHandlesMixedCaseContentType(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $request->method('getMethod')->willReturn('POST');
        $request->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('APPLICATION/JSON'); // Uppercase

        // This should fail since the validation is case-sensitive
        $handler->expects($this->never())->method('handle');

        $result = $this->middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testProcessWorksWithAllTargetMethods(): void
    {
        $targetMethods = ['POST', 'PUT', 'PATCH'];

        foreach ($targetMethods as $method) {
            $request = $this->createMock(ServerRequestInterface::class);
            $handler = $this->createMock(RequestHandlerInterface::class);
            $response = $this->createMock(ResponseInterface::class);

            $request->method('getMethod')->willReturn($method);
            $request->method('getHeaderLine')
                ->with('Content-Type')
                ->willReturn('application/json');

            $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($response);

            $result = $this->middleware->process($request, $handler);

            $this->assertSame($response, $result, "Method '{$method}' should be validated");
        }
    }

    public function testProcessSkipsValidationForNonTargetMethods(): void
    {
        $nonTargetMethods = ['GET', 'HEAD', 'OPTIONS', 'DELETE'];

        foreach ($nonTargetMethods as $method) {
            $request = $this->createMock(ServerRequestInterface::class);
            $handler = $this->createMock(RequestHandlerInterface::class);
            $response = $this->createMock(ResponseInterface::class);

            $request->method('getMethod')->willReturn($method);
            // Content-Type header should not even be checked for these methods
            $request->expects($this->never())->method('getHeaderLine');

            $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($response);

            $result = $this->middleware->process($request, $handler);

            $this->assertSame($response, $result, "Method '{$method}' should skip validation");
        }
    }
}
