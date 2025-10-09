<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Http\Middleware\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Http\Middleware\Impl\Utf8EncodingMiddleware;
use App\Application\Shared\Http\Middleware\Utf8EncodingMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Utf8EncodingMiddlewareTest extends TestCase
{
    private Utf8EncodingMiddleware $middleware;

    public function testImplementsUtf8EncodingMiddlewareInterface(): void
    {
        $this->assertInstanceOf(Utf8EncodingMiddlewareInterface::class, $this->middleware);
    }

    public function testProcessCallsHandlerAndReturnsResponse(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($request))
            ->willReturn($response);

        $response->method('getBody')->willReturn($stream);
        $stream->method('rewind');
        $stream->method('getContents')->willReturn('test content');
        $response->method('withBody')->willReturnSelf();

        $result = $this->middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testProcessHandlesDifferentContentTypes(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $handler->method('handle')->willReturn($response);
        $response->method('getBody')->willReturn($stream);
        $stream->method('rewind');
        $stream->method('getContents')->willReturn('test content');
        $response->method('withBody')->willReturnSelf();

        $contentTypes = [
            'application/json',
            'text/html',
            'text/plain',
            'application/xml',
            '',
        ];

        foreach ($contentTypes as $contentType) {
            $response->method('getHeaderLine')
                ->with('Content-Type')
                ->willReturn($contentType);

            $result = $this->middleware->process($request, $handler);
            $this->assertInstanceOf(ResponseInterface::class, $result);
        }
    }

    public function testProcessHandlesEmptyContent(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $handler->method('handle')->willReturn($response);
        $response->method('getBody')->willReturn($stream);
        $stream->method('rewind');
        $stream->method('getContents')->willReturn('');
        $response->expects($this->once())
            ->method('withBody')
            ->willReturnSelf();

        $result = $this->middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testProcessHandlesInvalidJsonGracefully(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $invalidJson = '{"invalid": json}';

        $handler->method('handle')->willReturn($response);
        $response->method('getBody')->willReturn($stream);
        $response->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('application/json');

        $stream->method('rewind');
        $stream->method('getContents')->willReturn($invalidJson);
        $response->expects($this->once())
            ->method('withBody')
            ->willReturnSelf();

        $result = $this->middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testProcessHandlesJsonResponse(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $jsonContent = '{"message":"Hello, 世界!","success":true}';

        $handler->method('handle')->willReturn($response);
        $response->method('getBody')->willReturn($stream);
        $response->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('application/json; charset=utf-8');

        $stream->method('rewind');
        $stream->method('getContents')->willReturn($jsonContent);
        $response->expects($this->once())
            ->method('withBody')
            ->willReturnSelf();

        $result = $this->middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testProcessHandlesNonJsonResponse(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $htmlContent = '<html><body>Hello</body></html>';

        $handler->method('handle')->willReturn($response);
        $response->method('getBody')->willReturn($stream);
        $response->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('text/html; charset=utf-8');

        $stream->method('rewind');
        $stream->method('getContents')->willReturn($htmlContent);
        $response->expects($this->once())
            ->method('withBody')
            ->willReturnSelf();

        $result = $this->middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testProcessHandlesValidUtf8Content(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $validUtf8Content = 'Hello, 世界!';

        $handler->method('handle')->willReturn($response);
        $response->method('getBody')->willReturn($stream);
        $stream->method('rewind');
        $stream->method('getContents')->willReturn($validUtf8Content);
        $response->expects($this->once())
            ->method('withBody')
            ->willReturnSelf();

        $result = $this->middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testProcessPreservesResponseStatus(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $handler->method('handle')->willReturn($response);
        $response->method('getBody')->willReturn($stream);
        $stream->method('rewind');
        $stream->method('getContents')->willReturn('content');

        // The middleware should not modify status code or other headers
        $response->expects($this->never())->method('withStatus');
        $response->expects($this->never())->method('withHeader');
        $response->expects($this->once())->method('withBody')->willReturnSelf();

        $this->middleware->process($request, $handler);
    }

    public function testProcessWorksWithMultipleRequests(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $handler->method('handle')->willReturn($response);
        $response->method('getBody')->willReturn($stream);
        $stream->method('rewind');
        $stream->method('getContents')->willReturn('content');
        $response->method('withBody')->willReturnSelf();

        // Test with multiple different requests
        for ($i = 0; $i < 3; $i++) {
            $request = $this->createMock(ServerRequestInterface::class);
            $result = $this->middleware->process($request, $handler);
            $this->assertInstanceOf(ResponseInterface::class, $result);
        }
    }

    protected function setUp(): void
    {
        $this->middleware = new Utf8EncodingMiddleware();
    }
}
