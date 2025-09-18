<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Application\ApplicationInterface;
use App\Application\Impl\ApiApplication;
use PHPUnit\Framework\TestCase;
use Slim\App as SlimApp;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;

abstract class AbstractBaseHttpIntegrationTest extends TestCase
{
    protected SlimApp $slimApp;
    protected ApplicationInterface $app;
    protected Psr17Factory $psr17Factory;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->app = ApiApplication::getInstance();
        $this->slimApp = $this->app->createSlimApp();
        $this->psr17Factory = new Psr17Factory();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function createRequest(string $method, string $path, array $body = null, array $headers = []): ServerRequest
    {
        $uri = new Uri('http://localhost' . $path);
        $request = new ServerRequest($method, $uri, $headers);
        
        if ($body !== null) {
            $request = $request->withBody(
                $this->psr17Factory->createStream(json_encode($body))
            );
            $request = $request->withHeader('Content-Type', 'application/json');
        }
        
        return $request;
    }

    protected function executeRequest(ServerRequest $request): array
    {
        $response = $this->slimApp->handle($request);
        $responseBody = $response->getBody()->getContents();
        
        $decodedBody = json_decode($responseBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $decodedBody = ['raw' => $responseBody];
        }
        
        return [
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => $decodedBody
        ];
    }

    protected function assertJsonResponse(array $response, int $expectedStatus = 200): void
    {
        $this->assertEquals($expectedStatus, $response['status']);
        $this->assertArrayHasKey('body', $response);
        $this->assertIsArray($response['body']);
    }

    protected function assertSuccessResponse(array $response): void
    {
        $this->assertJsonResponse($response);
        $this->assertTrue($response['body']['success'] ?? false);
    }

    protected function assertErrorResponse(array $response, int $expectedStatus = 400): void
    {
        $this->assertJsonResponse($response, $expectedStatus);
        $this->assertTrue($response['body']['error'] ?? false);
    }
}
