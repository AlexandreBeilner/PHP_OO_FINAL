<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Http\Middleware\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Http\Middleware\Impl\NotFoundHandlerMiddleware;
use App\Application\Shared\Http\Middleware\NotFoundHandlerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class NotFoundHandlerMiddlewareTest extends TestCase
{
    private NotFoundHandlerMiddleware $middleware;

    protected function setUp(): void
    {
        $this->middleware = new NotFoundHandlerMiddleware();
    }

    public function testImplementsNotFoundHandlerMiddlewareInterface(): void
    {
        $this->assertInstanceOf(NotFoundHandlerMiddlewareInterface::class, $this->middleware);
    }

    public function testProcessCallsHandlerAndReturnsResponse(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($request))
            ->willReturn($response);

        $response->method('getStatusCode')->willReturn(200);

        $result = $this->middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testProcessHandlesNon404ResponsesNormally(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $statusCodes = [200, 201, 400, 401, 403, 500];

        foreach ($statusCodes as $statusCode) {
            $handler->method('handle')->willReturn($response);
            $response->method('getStatusCode')->willReturn($statusCode);

            $result = $this->middleware->process($request, $handler);

            $this->assertSame($response, $result, "Status code {$statusCode} should pass through unchanged");
        }
    }

    public function testProcessHandles404ResponseWithJsonConversion(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $uri = $this->createMock(UriInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        // Setup request
        $uri->method('getPath')->willReturn('/api/unknown');
        $request->method('getUri')->willReturn($uri);
        $request->method('getMethod')->willReturn('GET');

        // Setup handler to return 404 response
        $handler->method('handle')->willReturn($response);
        $response->method('getStatusCode')->willReturn(404);
        $response->method('getBody')->willReturn($stream);

        // Expect JSON content to be written
        $stream->expects($this->once())
            ->method('write')
            ->willReturn(100); // Return number of bytes written
        
        // Expect Content-Type header to be set
        $response->expects($this->once())
            ->method('withHeader')
            ->with('Content-Type', 'application/json; charset=utf-8')
            ->willReturnSelf();

        $result = $this->middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testProcessIncludes404MetadataInResponse(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $uri = $this->createMock(UriInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $path = '/api/nonexistent';
        $method = 'POST';

        // Setup request with specific path and method
        $uri->method('getPath')->willReturn($path);
        $request->method('getUri')->willReturn($uri);
        $request->method('getMethod')->willReturn($method);

        $handler->method('handle')->willReturn($response);
        $response->method('getStatusCode')->willReturn(404);
        $response->method('getBody')->willReturn($stream);

        // Capture the JSON written to response
        $capturedJson = null;
        $stream->expects($this->once())
            ->method('write')
            ->willReturnCallback(function($content) use (&$capturedJson) {
                $capturedJson = $content;
                return strlen($content);
            });

        $response->method('withHeader')->willReturnSelf();

        $this->middleware->process($request, $handler);

        // Verify JSON contains expected metadata
        $this->assertNotNull($capturedJson);
        $decodedResponse = json_decode($capturedJson, true);
        
        $this->assertFalse($decodedResponse['success']);
        $this->assertEquals('Rota não encontrada', $decodedResponse['message']);
        $this->assertEquals($path, $decodedResponse['data']['path']);
        $this->assertEquals($method, $decodedResponse['data']['method']);
        $this->assertArrayHasKey('available_endpoints', $decodedResponse['data']);
        $this->assertIsArray($decodedResponse['data']['available_endpoints']);
    }

    public function testProcessIncludesAvailableEndpointsIn404(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $uri = $this->createMock(UriInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $uri->method('getPath')->willReturn('/invalid');
        $request->method('getUri')->willReturn($uri);
        $request->method('getMethod')->willReturn('GET');

        $handler->method('handle')->willReturn($response);
        $response->method('getStatusCode')->willReturn(404);
        $response->method('getBody')->willReturn($stream);

        $capturedJson = null;
        $stream->expects($this->once())
            ->method('write')
            ->willReturnCallback(function($content) use (&$capturedJson) {
                $capturedJson = $content;
                return strlen($content);
            });

        $response->method('withHeader')->willReturnSelf();

        $this->middleware->process($request, $handler);

        $decodedResponse = json_decode($capturedJson, true);
        $endpoints = $decodedResponse['data']['available_endpoints'];

        // Verify some expected endpoints exist
        $endpointPaths = array_column($endpoints, 'path');
        $this->assertContains('/', $endpointPaths);
        $this->assertContains('/health', $endpointPaths);
        $this->assertContains('/api/security/users', $endpointPaths);
        $this->assertContains('/api/auth/login', $endpointPaths);

        // Verify endpoint structure
        $firstEndpoint = $endpoints[0];
        $this->assertArrayHasKey('method', $firstEndpoint);
        $this->assertArrayHasKey('path', $firstEndpoint);
        $this->assertArrayHasKey('description', $firstEndpoint);
    }

    public function testProcessHandlesDifferentHttpMethods(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $uri = $this->createMock(UriInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

        foreach ($methods as $method) {
            $request = $this->createMock(ServerRequestInterface::class);
            
            $uri->method('getPath')->willReturn('/unknown');
            $request->method('getUri')->willReturn($uri);
            $request->method('getMethod')->willReturn($method);

            $handler->method('handle')->willReturn($response);
            $response->method('getStatusCode')->willReturn(404);
            $response->method('getBody')->willReturn($stream);
            $response->method('withHeader')->willReturnSelf();

            $stream->method('write')->willReturn(100);

            $result = $this->middleware->process($request, $handler);

            $this->assertSame($response, $result, "Method {$method} should be handled correctly");
        }
    }

    public function testProcessWorksWithDifferentPaths(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $uri = $this->createMock(UriInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $paths = [
            '/api/unknown',
            '/api/users/999',
            '/admin/panel',
            '/public/assets/missing.js',
            ''
        ];

        foreach ($paths as $path) {
            $uri->method('getPath')->willReturn($path);
            $request->method('getUri')->willReturn($uri);
            $request->method('getMethod')->willReturn('GET');

            $handler->method('handle')->willReturn($response);
            $response->method('getStatusCode')->willReturn(404);
            $response->method('getBody')->willReturn($stream);
            $response->method('withHeader')->willReturnSelf();

            $stream->method('write')->willReturn(100);

            $result = $this->middleware->process($request, $handler);

            $this->assertSame($response, $result, "Path '{$path}' should be handled correctly");
        }
    }

    public function testProcessOnlyModifies404Responses(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler->method('handle')->willReturn($response);
        $response->method('getStatusCode')->willReturn(200);

        // For non-404 responses, body and headers should not be modified
        $response->expects($this->never())->method('getBody');
        $response->expects($this->never())->method('withHeader');

        $result = $this->middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testProcessIsIdempotent(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $uri = $this->createMock(UriInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $uri->method('getPath')->willReturn('/test');
        $request->method('getUri')->willReturn($uri);
        $request->method('getMethod')->willReturn('GET');

        $handler->method('handle')->willReturn($response);
        $response->method('getStatusCode')->willReturn(404);
        $response->method('getBody')->willReturn($stream);
        $response->method('withHeader')->willReturnSelf();
        $stream->method('write')->willReturn(100);

        // Process the same request multiple times
        $result1 = $this->middleware->process($request, $handler);
        $result2 = $this->middleware->process($request, $handler);

        $this->assertSame($response, $result1);
        $this->assertSame($response, $result2);
    }
}