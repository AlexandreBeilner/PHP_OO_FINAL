<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\System\Services\Impl;

use PHPUnit\Framework\TestCase;
use App\Domain\System\Services\Impl\SystemService;
use App\Domain\System\Services\SystemServiceInterface;
use App\Infrastructure\Common\Database\DoctrineEntityManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\DBAL\Result;
use Exception;

final class SystemServiceTest extends TestCase
{
    private SystemService $systemService;
    private DoctrineEntityManagerInterface $doctrineManager;
    private EntityManagerInterface $entityManager;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->doctrineManager = $this->createMock(DoctrineEntityManagerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->connection = $this->createMock(Connection::class);
        $this->systemService = new SystemService($this->doctrineManager);
    }

    public function testImplementsSystemServiceInterface(): void
    {
        $this->assertInstanceOf(SystemServiceInterface::class, $this->systemService);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $service = new SystemService($this->doctrineManager);

        $this->assertInstanceOf(SystemService::class, $service);
        $this->assertInstanceOf(SystemServiceInterface::class, $service);
    }

    public function testGetSystemInfoReturnsArray(): void
    {
        $result = $this->systemService->getSystemInfo();

        $this->assertIsArray($result);
    }

    public function testGetSystemInfoStructure(): void
    {
        $result = $this->systemService->getSystemInfo();

        $expectedKeys = [
            'php_version', 'php_sapi', 'memory_limit', 'max_execution_time',
            'upload_max_filesize', 'post_max_size', 'timezone', 'server_time',
            'unix_timestamp', 'doctrine_version', 'slim_version', 'environment', 'debug_mode'
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $result, "Missing key: {$key}");
        }
    }

    public function testGetSystemInfoPHPVersion(): void
    {
        $result = $this->systemService->getSystemInfo();

        $this->assertEquals(PHP_VERSION, $result['php_version']);
        $this->assertIsString($result['php_version']);
    }

    public function testGetSystemInfoTimezone(): void
    {
        $result = $this->systemService->getSystemInfo();

        $this->assertEquals(date_default_timezone_get(), $result['timezone']);
        $this->assertIsString($result['timezone']);
    }

    public function testGetRequiredExtensionsStatusReturnsArray(): void
    {
        $result = $this->systemService->getRequiredExtensionsStatus();

        $this->assertIsArray($result);
    }

    public function testGetRequiredExtensionsStatusStructure(): void
    {
        $result = $this->systemService->getRequiredExtensionsStatus();

        $this->assertArrayHasKey('all_loaded', $result);
        $this->assertArrayHasKey('extensions', $result);
        $this->assertArrayHasKey('total_required', $result);
        $this->assertArrayHasKey('loaded_count', $result);
        $this->assertIsArray($result['extensions']);
    }

    public function testGetRequiredExtensionsChecksSpecificExtensions(): void
    {
        $result = $this->systemService->getRequiredExtensionsStatus();

        $extensions = $result['extensions'];
        $expectedExtensions = ['pdo', 'json', 'mbstring', 'openssl', 'curl'];

        foreach ($expectedExtensions as $extension) {
            $this->assertArrayHasKey($extension, $extensions);
            $this->assertArrayHasKey('loaded', $extensions[$extension]);
            $this->assertArrayHasKey('description', $extensions[$extension]);
            $this->assertIsBool($extensions[$extension]['loaded']);
            $this->assertIsString($extensions[$extension]['description']);
        }
    }

    public function testRemoveDirectoryWithNonExistentDirectory(): void
    {
        $result = $this->systemService->removeDirectory('/non/existent/path');

        $this->assertTrue($result);
    }

    public function testClearCacheReturnsArray(): void
    {
        $result = $this->systemService->clearCache();

        $this->assertIsArray($result);
    }

    public function testClearCacheStructure(): void
    {
        $result = $this->systemService->clearCache();

        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertContains($result['status'], ['success', 'warning', 'error']);
        $this->assertIsString($result['message']);
    }

    public function testTestDatabaseWithSuccessfulConnection(): void
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturn(['test' => 1]);

        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        $metadataFactory->method('getAllMetadata')->willReturn([]);

        $this->connection->method('connect');
        $this->connection->method('executeQuery')->willReturn($result);

        $this->entityManager->method('getConnection')->willReturn($this->connection);
        $this->entityManager->method('getMetadataFactory')->willReturn($metadataFactory);

        $this->doctrineManager->method('getMaster')->willReturn($this->entityManager);

        $response = $this->systemService->testDatabase();

        $this->assertIsArray($response);
        $this->assertEquals('success', $response['status']);
        $this->assertArrayHasKey('connection_test', $response);
        $this->assertArrayHasKey('entities_count', $response);
        $this->assertArrayHasKey('entities', $response);
    }

    public function testTestDatabaseWithConnectionFailure(): void
    {
        $this->connection->method('connect')
            ->willThrowException(new Exception('Connection failed'));

        $this->entityManager->method('getConnection')->willReturn($this->connection);
        $this->doctrineManager->method('getMaster')->willReturn($this->entityManager);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Erro ao conectar com banco de dados: Connection failed');

        $this->systemService->testDatabase();
    }

    public function testSystemServiceIsStateless(): void
    {
        $manager1 = $this->createMock(DoctrineEntityManagerInterface::class);
        $manager2 = $this->createMock(DoctrineEntityManagerInterface::class);

        $service1 = new SystemService($manager1);
        $service2 = new SystemService($manager2);

        // Both should produce same system info structure (different instances)
        $info1 = $service1->getSystemInfo();
        $info2 = $service2->getSystemInfo();

        $this->assertEquals(array_keys($info1), array_keys($info2));
        $this->assertNotSame($service1, $service2);
    }

    public function testGetRequiredExtensionsAllLoadedFlag(): void
    {
        $result = $this->systemService->getRequiredExtensionsStatus();

        $this->assertIsBool($result['all_loaded']);
        $this->assertIsInt($result['total_required']);
        $this->assertIsInt($result['loaded_count']);
        $this->assertLessThanOrEqual($result['total_required'], $result['loaded_count']);
    }

    public function testMultipleMethodCallsAreConsistent(): void
    {
        // System info should be consistent across calls
        $info1 = $this->systemService->getSystemInfo();
        $info2 = $this->systemService->getSystemInfo();

        $this->assertEquals($info1['php_version'], $info2['php_version']);
        $this->assertEquals($info1['timezone'], $info2['timezone']);

        // Extensions status should be consistent
        $ext1 = $this->systemService->getRequiredExtensionsStatus();
        $ext2 = $this->systemService->getRequiredExtensionsStatus();

        $this->assertEquals($ext1['total_required'], $ext2['total_required']);
        $this->assertEquals($ext1['all_loaded'], $ext2['all_loaded']);
    }

    public function testConstructorRequiresDependencies(): void
    {
        $manager = $this->createMock(DoctrineEntityManagerInterface::class);
        $service = new SystemService($manager);

        $this->assertInstanceOf(SystemService::class, $service);
    }

    public function testServiceIntegrationWithDoctrineManager(): void
    {
        // Test that service integrates properly with DoctrineEntityManagerInterface
        $this->doctrineManager->expects($this->never())
            ->method('getMaster');

        // Methods that don't use doctrine should work without database
        $systemInfo = $this->systemService->getSystemInfo();
        $extensionsInfo = $this->systemService->getRequiredExtensionsStatus();
        $removeResult = $this->systemService->removeDirectory('/non/existent');

        $this->assertIsArray($systemInfo);
        $this->assertIsArray($extensionsInfo);
        $this->assertTrue($removeResult);
    }
}
