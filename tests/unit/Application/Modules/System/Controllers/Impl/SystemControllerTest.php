<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Modules\System\Controllers\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Modules\System\Controllers\Impl\SystemController;
use App\Application\Modules\System\Controllers\SystemControllerInterface;
use App\Domain\System\Services\SystemServiceInterface;
use App\Domain\System\Services\SystemResponseServiceInterface;
use App\Application\Shared\DTOs\ApiResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class SystemControllerTest extends TestCase
{
    private SystemController $systemController;
    private SystemServiceInterface $systemService;
    private SystemResponseServiceInterface $systemResponseService;
    private ServerRequestInterface $request;
    private ResponseInterface $response;
    private StreamInterface $body;

    protected function setUp(): void
    {
        $this->systemService = $this->createMock(SystemServiceInterface::class);
        $this->systemResponseService = $this->createMock(SystemResponseServiceInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->body = $this->createMock(StreamInterface::class);

        $this->systemController = new SystemController(
            $this->systemService,
            $this->systemResponseService
        );
    }

    public function testImplementsExpectedInterface(): void
    {
        $this->assertInstanceOf(SystemControllerInterface::class, $this->systemController);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $systemService = $this->createMock(SystemServiceInterface::class);
        $systemResponseService = $this->createMock(SystemResponseServiceInterface::class);
        
        $instance = new SystemController($systemService, $systemResponseService);

        $this->assertInstanceOf(SystemController::class, $instance);
        $this->assertInstanceOf(SystemControllerInterface::class, $instance);
    }

    public function testConstructorWithDifferentServices(): void
    {
        $systemService1 = $this->createMock(SystemServiceInterface::class);
        $systemResponseService1 = $this->createMock(SystemResponseServiceInterface::class);
        $systemService2 = $this->createMock(SystemServiceInterface::class);
        $systemResponseService2 = $this->createMock(SystemResponseServiceInterface::class);

        $instance1 = new SystemController($systemService1, $systemResponseService1);
        $instance2 = new SystemController($systemService2, $systemResponseService2);

        $this->assertInstanceOf(SystemController::class, $instance1);
        $this->assertInstanceOf(SystemController::class, $instance2);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testGetRequiredExtensionsStatusSuccess(): void
    {
        $extensionsData = [
            'json' => true,
            'mysqli' => true,
            'pdo' => true
        ];

        $apiResponse = $this->createMock(ApiResponseInterface::class);
        $apiResponse->method('toJson')->willReturn('{"status":"success"}');
        $apiResponse->method('getCode')->willReturn(200);

        $this->systemService
            ->expects($this->once())
            ->method('getRequiredExtensionsStatus')
            ->willReturn($extensionsData);

        $this->response->method('getBody')->willReturn($this->body);
        $this->body->expects($this->once())->method('write');
        $this->response
            ->method('withHeader')
            ->with('Content-Type', 'application/json')
            ->willReturnSelf();
        $this->response
            ->method('withStatus')
            ->with(200)
            ->willReturnSelf();

        $result = $this->systemController->getRequiredExtensionsStatus($this->request, $this->response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testGetRequiredExtensionsStatusWithException(): void
    {
        $this->systemService
            ->expects($this->once())
            ->method('getRequiredExtensionsStatus')
            ->willThrowException(new \Exception('Test exception'));

        $this->response->method('getBody')->willReturn($this->body);
        $this->body->expects($this->once())->method('write');
        $this->response
            ->method('withHeader')
            ->with('Content-Type', 'application/json')
            ->willReturnSelf();
        $this->response
            ->method('withStatus')
            ->with(500)
            ->willReturnSelf();

        $result = $this->systemController->getRequiredExtensionsStatus($this->request, $this->response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testGetSystemInfoSuccess(): void
    {
        $systemInfo = [
            'php_version' => '7.4.33',
            'memory_limit' => '128M'
        ];

        $responseData = [
            'environment' => 'test',
            'php_version' => '7.4.33'
        ];

        $this->systemService
            ->expects($this->once())
            ->method('getSystemInfo')
            ->willReturn($systemInfo);

        $this->systemResponseService
            ->expects($this->once())
            ->method('buildSystemInfoResponse')
            ->with($systemInfo)
            ->willReturn($responseData);

        $this->response->method('getBody')->willReturn($this->body);
        $this->body->expects($this->once())->method('write');
        $this->response
            ->method('withHeader')
            ->with('Content-Type', 'application/json')
            ->willReturnSelf();
        $this->response
            ->method('withStatus')
            ->with(200)
            ->willReturnSelf();

        $result = $this->systemController->getSystemInfo($this->request, $this->response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testGetAppInfoCallsGetSystemInfo(): void
    {
        $systemInfo = [
            'php_version' => '7.4.33',
            'memory_limit' => '128M'
        ];

        $responseData = [
            'environment' => 'test',
            'php_version' => '7.4.33'
        ];

        $this->systemService
            ->expects($this->once())
            ->method('getSystemInfo')
            ->willReturn($systemInfo);

        $this->systemResponseService
            ->expects($this->once())
            ->method('buildSystemInfoResponse')
            ->with($systemInfo)
            ->willReturn($responseData);

        $this->response->method('getBody')->willReturn($this->body);
        $this->body->expects($this->once())->method('write');
        $this->response
            ->method('withHeader')
            ->with('Content-Type', 'application/json')
            ->willReturnSelf();
        $this->response
            ->method('withStatus')
            ->with(200)
            ->willReturnSelf();

        $result = $this->systemController->getAppInfo($this->request, $this->response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testTestDatabaseSuccess(): void
    {
        $testResult = [
            'master_connected' => true,
            'slave_connected' => true,
            'status' => 'success'
        ];

        $this->systemService
            ->expects($this->once())
            ->method('testDatabase')
            ->willReturn($testResult);

        $this->response->method('getBody')->willReturn($this->body);
        $this->body->expects($this->once())->method('write');
        $this->response
            ->method('withHeader')
            ->with('Content-Type', 'application/json')
            ->willReturnSelf();
        $this->response
            ->method('withStatus')
            ->with(200)
            ->willReturnSelf();

        $result = $this->systemController->testDatabase($this->request, $this->response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testTestDatabaseWithException(): void
    {
        $this->systemService
            ->expects($this->once())
            ->method('testDatabase')
            ->willThrowException(new \Exception('Database test failed'));

        $this->response->method('getBody')->willReturn($this->body);
        $this->body->expects($this->once())->method('write');
        $this->response
            ->method('withHeader')
            ->with('Content-Type', 'application/json')
            ->willReturnSelf();
        $this->response
            ->method('withStatus')
            ->with(500)
            ->willReturnSelf();

        $result = $this->systemController->testDatabase($this->request, $this->response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testTestDoctrineSuccess(): void
    {
        $testResult = [
            'master_connected' => true,
            'slave_connected' => true,
            'status' => 'success'
        ];

        $this->systemService
            ->expects($this->once())
            ->method('testDatabase')
            ->willReturn($testResult);

        $this->response->method('getBody')->willReturn($this->body);
        $this->body->expects($this->once())->method('write');
        $this->response
            ->method('withHeader')
            ->with('Content-Type', 'application/json')
            ->willReturnSelf();
        $this->response
            ->method('withStatus')
            ->with(200)
            ->willReturnSelf();

        $result = $this->systemController->testDoctrine($this->request, $this->response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testTestDoctrineWithException(): void
    {
        $this->systemService
            ->expects($this->once())
            ->method('testDatabase')
            ->willThrowException(new \Exception('Doctrine connection failed'));

        $this->response->method('getBody')->willReturn($this->body);
        $this->body->expects($this->once())->method('write');
        $this->response
            ->method('withHeader')
            ->with('Content-Type', 'application/json')
            ->willReturnSelf();
        $this->response
            ->method('withStatus')
            ->with(500)
            ->willReturnSelf();

        $result = $this->systemController->testDoctrine($this->request, $this->response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(SystemController::class);
        
        $this->assertTrue($reflection->isFinal());
    }

    public function testAllControllerMethodsReturnResponseInterface(): void
    {
        $controllerMethods = [
            'getAppInfo',
            'getRequiredExtensionsStatus', 
            'getSystemInfo',
            'testDatabase',
            'testDoctrine'
        ];

        $reflection = new \ReflectionClass(SystemController::class);
        
        foreach ($controllerMethods as $methodName) {
            $method = $reflection->getMethod($methodName);
            $returnType = $method->getReturnType();
            
            $this->assertNotNull($returnType, "Method {$methodName} should have return type");
            $this->assertEquals(ResponseInterface::class, $returnType->getName(), 
                "Method {$methodName} should return ResponseInterface");
        }
    }
}
