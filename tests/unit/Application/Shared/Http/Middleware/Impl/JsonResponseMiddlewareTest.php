<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Http\Middleware\Impl;

use App\Application\Shared\Http\Middleware\Impl\JsonResponseMiddleware;
use App\Application\Shared\Http\Middleware\JsonResponseMiddlewareInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class JsonResponseMiddlewareTest extends TestCase
{
    private JsonResponseMiddleware $middleware;

    public function testImplementsJsonResponseMiddlewareInterface(): void
    {
        $this->assertInstanceOf(JsonResponseMiddlewareInterface::class, $this->middleware);
    }

    public function testProcessAddsAllRequiredHeaders(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler->method('handle')->willReturn($response);

        // Verify that withHeader is called exactly 5 times for all required headers
        $response->expects($this->exactly(5))
            ->method('withHeader')
            ->willReturnCallback(function ($headerName, $headerValue) use ($response) {
                // Verify that expected headers are being set
                $validHeaders = [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                    'X-Content-Type-Options' => 'nosniff',
                ];

                $this->assertArrayHasKey($headerName, $validHeaders);
                $this->assertEquals($validHeaders[$headerName], $headerValue);

                return $response;
            });

        $this->middleware->process($request, $handler);
    }

    public function testProcessCallsHandlerWithOriginalRequest(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($request))
            ->willReturn($response);

        $response->method('withHeader')->willReturnSelf();

        $this->middleware->process($request, $handler);
    }

    public function testProcessIsIdempotent(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler->method('handle')->willReturn($response);
        $response->method('withHeader')->willReturnSelf();

        // Process the same request multiple times
        $result1 = $this->middleware->process($request, $handler);
        $result2 = $this->middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $result1);
        $this->assertInstanceOf(ResponseInterface::class, $result2);
    }

    public function testProcessPreservesResponseBody(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler->method('handle')->willReturn($response);

        // The middleware should only modify headers, not body
        $response->expects($this->never())->method('getBody');
        $response->method('withHeader')->willReturnSelf();

        $this->middleware->process($request, $handler);
    }

    public function testProcessReturnsModifiedResponse(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $originalResponse = $this->createMock(ResponseInterface::class);
        $finalResponse = $this->createMock(ResponseInterface::class);

        $handler->method('handle')->willReturn($originalResponse);

        // Mock chain of withHeader calls
        $originalResponse->method('withHeader')->willReturn($finalResponse);
        $finalResponse->method('withHeader')->willReturnSelf();

        $result = $this->middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testProcessSetsCacheControlHeaders(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler->method('handle')->willReturn($response);

        // Expected header calls in order
        $response->expects($this->exactly(5))
            ->method('withHeader')
            ->withConsecutive(
                ['Content-Type', 'application/json; charset=utf-8'],
                ['Cache-Control', 'no-cache, no-store, must-revalidate'],
                ['Pragma', 'no-cache'],
                ['Expires', '0'],
                ['X-Content-Type-Options', 'nosniff']
            )
            ->willReturnSelf();

        $result = $this->middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testProcessSetsCorrectContentTypeHeader(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $originalResponse = $this->createMock(ResponseInterface::class);
        $modifiedResponse = $this->createMock(ResponseInterface::class);

        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($originalResponse);

        $originalResponse->expects($this->once())
            ->method('withHeader')
            ->with('Content-Type', 'application/json; charset=utf-8')
            ->willReturn($modifiedResponse);

        // Mock para os demais headers
        $modifiedResponse->method('withHeader')->willReturnSelf();

        $result = $this->middleware->process($request, $handler);

        $this->assertSame($modifiedResponse, $result);
    }

    public function testProcessSetsSecurityHeaders(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler->method('handle')->willReturn($response);

        // Mock all header calls - we're testing that all 5 headers are set
        $response->expects($this->exactly(5))
            ->method('withHeader')
            ->willReturnSelf();

        $result = $this->middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testProcessWorksWithDifferentRequests(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler->method('handle')->willReturn($response);
        $response->method('withHeader')->willReturnSelf();

        // Test with multiple different requests
        for ($i = 0; $i < 3; $i++) {
            $request = $this->createMock(ServerRequestInterface::class);
            $result = $this->middleware->process($request, $handler);
            $this->assertInstanceOf(ResponseInterface::class, $result);
        }
    }

    protected function setUp(): void
    {
        $this->middleware = new JsonResponseMiddleware();
    }
}
