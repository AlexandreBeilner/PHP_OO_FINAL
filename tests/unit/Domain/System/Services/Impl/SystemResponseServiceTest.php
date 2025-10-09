<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\System\Services\Impl;

use PHPUnit\Framework\TestCase;
use App\Domain\System\Services\Impl\SystemResponseService;
use App\Domain\System\Services\SystemResponseServiceInterface;

final class SystemResponseServiceTest extends TestCase
{
    private SystemResponseService $systemResponseService;

    protected function setUp(): void
    {
        $this->systemResponseService = new SystemResponseService();
    }

    public function testImplementsSystemResponseServiceInterface(): void
    {
        $this->assertInstanceOf(SystemResponseServiceInterface::class, $this->systemResponseService);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $service = new SystemResponseService();

        $this->assertInstanceOf(SystemResponseService::class, $service);
        $this->assertInstanceOf(SystemResponseServiceInterface::class, $service);
    }

    public function testBuildSystemInfoResponseReturnsArray(): void
    {
        $systemInfo = ['php_version' => '7.4.33'];
        
        $result = $this->systemResponseService->buildSystemInfoResponse($systemInfo);

        $this->assertIsArray($result);
    }

    public function testBuildSystemInfoResponseStructure(): void
    {
        $systemInfo = ['php_version' => '7.4.33', 'memory_limit' => '128M'];
        
        $result = $this->systemResponseService->buildSystemInfoResponse($systemInfo);

        $this->assertArrayHasKey('app', $result);
        $this->assertArrayHasKey('version', $result);
        $this->assertArrayHasKey('system', $result);
        $this->assertArrayHasKey('environment_info', $result);
    }

    public function testBuildSystemInfoResponseAppName(): void
    {
        $result = $this->systemResponseService->buildSystemInfoResponse([]);

        $this->assertEquals('Projeto de Treinamento PHP-OO', $result['app']);
    }

    public function testBuildSystemInfoResponseVersion(): void
    {
        $result = $this->systemResponseService->buildSystemInfoResponse([]);

        $this->assertEquals('1.0.0', $result['version']);
    }

    public function testBuildSystemInfoResponseIncludesSystemInfo(): void
    {
        $systemInfo = ['php_version' => '7.4.33', 'environment' => 'development'];
        
        $result = $this->systemResponseService->buildSystemInfoResponse($systemInfo);

        $this->assertEquals($systemInfo, $result['system']);
    }

    public function testBuildSystemInfoResponseWithEmptySystemInfo(): void
    {
        $result = $this->systemResponseService->buildSystemInfoResponse([]);

        $this->assertEquals([], $result['system']);
    }

    public function testBuildSystemInfoResponseIncludesEnvironmentInfo(): void
    {
        $systemInfo = ['test' => 'data'];
        
        $result = $this->systemResponseService->buildSystemInfoResponse($systemInfo);

        $this->assertIsArray($result['environment_info']);
        $this->assertNotEmpty($result['environment_info']);
    }

    public function testBuildEnvironmentInfoReturnsArray(): void
    {
        $result = $this->systemResponseService->buildEnvironmentInfo();

        $this->assertIsArray($result);
    }

    public function testBuildEnvironmentInfoStructure(): void
    {
        $result = $this->systemResponseService->buildEnvironmentInfo();

        $this->assertArrayHasKey('working_directory', $result);
        $this->assertArrayHasKey('script_path', $result);
        $this->assertArrayHasKey('vendor_path', $result);
        $this->assertArrayHasKey('cache_path', $result);
        $this->assertArrayHasKey('docker_environment', $result);
    }

    public function testBuildEnvironmentInfoWorkingDirectory(): void
    {
        $result = $this->systemResponseService->buildEnvironmentInfo();

        $this->assertIsString($result['working_directory']);
        $this->assertEquals(getcwd(), $result['working_directory']);
    }

    public function testBuildEnvironmentInfoScriptPath(): void
    {
        $result = $this->systemResponseService->buildEnvironmentInfo();

        $this->assertIsString($result['script_path']);
        $this->assertStringContainsString('SystemResponseService.php', $result['script_path']);
    }

    public function testBuildEnvironmentInfoVendorPath(): void
    {
        $result = $this->systemResponseService->buildEnvironmentInfo();

        // vendor_path can be false if path doesn't exist, or string if it exists
        $this->assertTrue(is_string($result['vendor_path']) || $result['vendor_path'] === false);
    }

    public function testBuildEnvironmentInfoCachePath(): void
    {
        $result = $this->systemResponseService->buildEnvironmentInfo();

        // cache_path can be false if path doesn't exist, or string if it exists
        $this->assertTrue(is_string($result['cache_path']) || $result['cache_path'] === false);
    }

    public function testBuildEnvironmentInfoDockerEnvironment(): void
    {
        $result = $this->systemResponseService->buildEnvironmentInfo();

        $this->assertContains($result['docker_environment'], ['Sim', 'Não']);
    }

    public function testBuildSystemInfoResponseConsistency(): void
    {
        $systemInfo = ['test' => 'data'];
        
        $result1 = $this->systemResponseService->buildSystemInfoResponse($systemInfo);
        $result2 = $this->systemResponseService->buildSystemInfoResponse($systemInfo);

        // App and version should be consistent
        $this->assertEquals($result1['app'], $result2['app']);
        $this->assertEquals($result1['version'], $result2['version']);
        $this->assertEquals($result1['system'], $result2['system']);
    }

    public function testBuildEnvironmentInfoConsistency(): void
    {
        $result1 = $this->systemResponseService->buildEnvironmentInfo();
        $result2 = $this->systemResponseService->buildEnvironmentInfo();

        // Most environment info should be consistent
        $this->assertEquals($result1['working_directory'], $result2['working_directory']);
        $this->assertEquals($result1['script_path'], $result2['script_path']);
        $this->assertEquals($result1['docker_environment'], $result2['docker_environment']);
    }

    public function testServiceIsStateless(): void
    {
        $service1 = new SystemResponseService();
        $service2 = new SystemResponseService();

        $systemInfo = ['test' => 'value'];
        
        $result1 = $service1->buildSystemInfoResponse($systemInfo);
        $result2 = $service2->buildSystemInfoResponse($systemInfo);

        // Different instances should produce identical results
        $this->assertEquals($result1['app'], $result2['app']);
        $this->assertEquals($result1['version'], $result2['version']);
        $this->assertEquals($result1['system'], $result2['system']);
    }

    public function testBuildSystemInfoResponseWithComplexSystemInfo(): void
    {
        $systemInfo = [
            'php_version' => '7.4.33',
            'memory_limit' => '128M',
            'extensions' => ['pdo', 'json', 'mbstring'],
            'nested' => [
                'database' => 'PostgreSQL',
                'version' => '13.0'
            ]
        ];
        
        $result = $this->systemResponseService->buildSystemInfoResponse($systemInfo);

        $this->assertEquals($systemInfo, $result['system']);
        $this->assertIsArray($result['system']['extensions']);
        $this->assertIsArray($result['system']['nested']);
    }

    public function testBuildSystemInfoResponseStructureIsComplete(): void
    {
        $result = $this->systemResponseService->buildSystemInfoResponse(['test' => 'data']);

        // Ensure all required keys are present
        $requiredKeys = ['app', 'version', 'system', 'environment_info'];
        
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $result, "Missing required key: {$key}");
        }

        // Ensure we have exactly the expected keys (no extra keys)
        $this->assertCount(4, $result);
    }
}
