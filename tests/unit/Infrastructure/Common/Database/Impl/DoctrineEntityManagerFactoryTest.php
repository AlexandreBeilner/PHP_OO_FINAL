<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Common\Database\Impl;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Common\Database\Impl\DoctrineEntityManagerFactory;
use App\Infrastructure\Common\Database\DoctrineEntityManagerFactoryInterface;
use App\Infrastructure\Common\Database\DoctrineEntityManagerInterface;
use App\Application\Shared\Orchestrator\BootstrapOrchestratorInterface;

final class DoctrineEntityManagerFactoryTest extends TestCase
{
    private array $connectionConfig;

    protected function setUp(): void
    {
        $this->connectionConfig = [
            'connections' => [
                'master' => [
                    'host' => 'localhost',
                    'port' => 5432,
                    'dbname' => 'test_db',
                    'user' => 'test_user',
                    'password' => 'test_pass',
                ],
                'slave' => [
                    'host' => 'slave-host',
                    'port' => 5433,
                    'dbname' => 'test_db_slave',
                    'user' => 'slave_user',
                    'password' => 'slave_pass',
                ],
            ],
        ];
    }

    public function testImplementsExpectedInterface(): void
    {
        $this->assertTrue(is_subclass_of(DoctrineEntityManagerFactory::class, DoctrineEntityManagerFactoryInterface::class));
    }

    public function testCreateReturnsDoctrineEntityManagerInterface(): void
    {
        $result = DoctrineEntityManagerFactory::create($this->connectionConfig);

        $this->assertInstanceOf(DoctrineEntityManagerInterface::class, $result);
    }

    public function testCreateWithNullOrchestratorReturnsValidInstance(): void
    {
        $result = DoctrineEntityManagerFactory::create($this->connectionConfig, null);

        $this->assertInstanceOf(DoctrineEntityManagerInterface::class, $result);
    }

    public function testCreateWithOrchestratorCallsCollectAllEntityPaths(): void
    {
        $orchestrator = $this->createMock(BootstrapOrchestratorInterface::class);
        $expectedEntityPaths = ['/path/to/entities'];

        $orchestrator->expects($this->once())
            ->method('collectAllEntityPaths')
            ->willReturn($expectedEntityPaths);

        $result = DoctrineEntityManagerFactory::create($this->connectionConfig, $orchestrator);

        $this->assertInstanceOf(DoctrineEntityManagerInterface::class, $result);
    }

    public function testCreateWithOrchestratorWithoutEntityPathsReturnsValidInstance(): void
    {
        $orchestrator = $this->createMock(BootstrapOrchestratorInterface::class);

        $orchestrator->expects($this->once())
            ->method('collectAllEntityPaths')
            ->willReturn([]);

        $result = DoctrineEntityManagerFactory::create($this->connectionConfig, $orchestrator);

        $this->assertInstanceOf(DoctrineEntityManagerInterface::class, $result);
    }

    public function testCreateWithValidConnectionConfigCreatesInstance(): void
    {
        $config = [
            'connections' => [
                'master' => [
                    'host' => '127.0.0.1',
                    'port' => 5432,
                    'dbname' => 'production_db',
                    'user' => 'prod_user',
                    'password' => 'secure_password',
                ],
                'slave' => [
                    'host' => '192.168.1.100',
                    'port' => 5432,
                    'dbname' => 'production_db_slave',
                    'user' => 'slave_user',
                    'password' => 'slave_password',
                ],
            ],
        ];

        $result = DoctrineEntityManagerFactory::create($config);

        $this->assertInstanceOf(DoctrineEntityManagerInterface::class, $result);
    }

    public function testCreateWithMultipleEntityPathsFromOrchestrator(): void
    {
        $orchestrator = $this->createMock(BootstrapOrchestratorInterface::class);
        $multipleEntityPaths = [
            '/path/to/domain/entities',
            '/path/to/security/entities',
            '/path/to/common/entities',
        ];

        $orchestrator->expects($this->once())
            ->method('collectAllEntityPaths')
            ->willReturn($multipleEntityPaths);

        $result = DoctrineEntityManagerFactory::create($this->connectionConfig, $orchestrator);

        $this->assertInstanceOf(DoctrineEntityManagerInterface::class, $result);
    }

    public function testCreateIsStaticMethod(): void
    {
        $reflection = new \ReflectionClass(DoctrineEntityManagerFactory::class);
        $method = $reflection->getMethod('create');

        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }
}