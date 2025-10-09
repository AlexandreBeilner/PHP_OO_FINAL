<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Loader\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Loader\Impl\BootstrapLoader;
use App\Application\Shared\Loader\BootstrapLoaderInterface;
use App\Application\Shared\Registry\BootstrapRegistryInterface;
use App\Application\Shared\BootstrapInterface;
use DI\ContainerBuilder;

final class BootstrapLoaderTest extends TestCase
{
    private BootstrapLoader $bootstrapLoader;
    private BootstrapRegistryInterface $registry;
    private ContainerBuilder $builder;

    protected function setUp(): void
    {
        $this->bootstrapLoader = new BootstrapLoader();
        $this->registry = $this->createMock(BootstrapRegistryInterface::class);
        $this->builder = $this->createMock(ContainerBuilder::class);
    }

    public function testImplementsBootstrapLoaderInterface(): void
    {
        $this->assertInstanceOf(BootstrapLoaderInterface::class, $this->bootstrapLoader);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $loader = new BootstrapLoader();

        $this->assertInstanceOf(BootstrapLoader::class, $loader);
        $this->assertInstanceOf(BootstrapLoaderInterface::class, $loader);
    }

    public function testLoadAllVoidReturnType(): void
    {
        $this->registry->expects($this->once())
            ->method('getAllSortedByPriority')
            ->willReturn([]);

        $result = $this->bootstrapLoader->loadAll($this->registry, $this->builder);

        $this->assertNull($result);
    }

    public function testLoadAllWithEmptyBootstraps(): void
    {
        
        $this->registry->expects($this->once())
            ->method('getAllSortedByPriority')
            ->willReturn([]);

        // Should not throw exception
        $this->bootstrapLoader->loadAll($this->registry, $this->builder);
    }

    public function testLoadAllWithSingleBootstrap(): void
    {
        $bootstrap = $this->createMock(BootstrapInterface::class);
        $bootstrap->expects($this->once())
            ->method('register')
            ->with($this->builder);

        $this->registry->expects($this->once())
            ->method('getAllSortedByPriority')
            ->willReturn([$bootstrap]);

        $this->bootstrapLoader->loadAll($this->registry, $this->builder);
    }

    public function testLoadAllWithMultipleBootstraps(): void
    {
        $bootstrap1 = $this->createMock(BootstrapInterface::class);
        $bootstrap2 = $this->createMock(BootstrapInterface::class);
        $bootstrap3 = $this->createMock(BootstrapInterface::class);

        // All bootstraps should have register called with the builder
        $bootstrap1->expects($this->once())->method('register')->with($this->builder);
        $bootstrap2->expects($this->once())->method('register')->with($this->builder);
        $bootstrap3->expects($this->once())->method('register')->with($this->builder);

        $this->registry->expects($this->once())
            ->method('getAllSortedByPriority')
            ->willReturn([$bootstrap1, $bootstrap2, $bootstrap3]);

        $this->bootstrapLoader->loadAll($this->registry, $this->builder);
    }

    public function testLoadAllCallsGetAllSortedByPriorityOnRegistry(): void
    {
        $this->registry->expects($this->once())
            ->method('getAllSortedByPriority')
            ->willReturn([]);

        $this->bootstrapLoader->loadAll($this->registry, $this->builder);
    }

    public function testLoadAllProcessesBootstrapsInOrder(): void
    {
        $callOrder = [];

        $bootstrap1 = $this->createMock(BootstrapInterface::class);
        $bootstrap1->expects($this->once())
            ->method('register')
            ->with($this->builder)
            ->willReturnCallback(function() use (&$callOrder) {
                $callOrder[] = 'bootstrap1';
            });

        $bootstrap2 = $this->createMock(BootstrapInterface::class);
        $bootstrap2->expects($this->once())
            ->method('register')
            ->with($this->builder)
            ->willReturnCallback(function() use (&$callOrder) {
                $callOrder[] = 'bootstrap2';
            });

        $this->registry->expects($this->once())
            ->method('getAllSortedByPriority')
            ->willReturn([$bootstrap1, $bootstrap2]);

        $this->bootstrapLoader->loadAll($this->registry, $this->builder);

        $this->assertEquals(['bootstrap1', 'bootstrap2'], $callOrder);
    }

    public function testLoadAllWithSameRegistryAndBuilderMultipleTimes(): void
    {
        $bootstrap = $this->createMock(BootstrapInterface::class);
        $bootstrap->expects($this->exactly(2))
            ->method('register')
            ->with($this->builder);

        $this->registry->expects($this->exactly(2))
            ->method('getAllSortedByPriority')
            ->willReturn([$bootstrap]);

        // Should be able to call multiple times
        $this->bootstrapLoader->loadAll($this->registry, $this->builder);
        $this->bootstrapLoader->loadAll($this->registry, $this->builder);
    }

    public function testLoadAllStatelessBehavior(): void
    {
        $loader1 = new BootstrapLoader();
        $loader2 = new BootstrapLoader();

        $bootstrap = $this->createMock(BootstrapInterface::class);
        $bootstrap->expects($this->exactly(2))
            ->method('register')
            ->with($this->builder);

        $this->registry->expects($this->exactly(2))
            ->method('getAllSortedByPriority')
            ->willReturn([$bootstrap]);

        // Different instances should behave identically
        $loader1->loadAll($this->registry, $this->builder);
        $loader2->loadAll($this->registry, $this->builder);

    }

    public function testLoadAllHandlesRegistryReturnValue(): void
    {
        // Test that the method properly handles the array returned by registry
        $bootstraps = [
            $this->createMock(BootstrapInterface::class),
            $this->createMock(BootstrapInterface::class)
        ];

        foreach ($bootstraps as $bootstrap) {
            $bootstrap->expects($this->once())
                ->method('register')
                ->with($this->builder);
        }

        $this->registry->expects($this->once())
            ->method('getAllSortedByPriority')
            ->willReturn($bootstraps);

        $this->bootstrapLoader->loadAll($this->registry, $this->builder);

    }

    public function testLoadAllWithDifferentBuilders(): void
    {
        $builder1 = $this->createMock(ContainerBuilder::class);
        $builder2 = $this->createMock(ContainerBuilder::class);

        $bootstrap1 = $this->createMock(BootstrapInterface::class);
        $bootstrap2 = $this->createMock(BootstrapInterface::class);

        $bootstrap1->expects($this->once())->method('register')->with($builder1);
        $bootstrap2->expects($this->once())->method('register')->with($builder2);

        $this->registry->expects($this->exactly(2))
            ->method('getAllSortedByPriority')
            ->willReturnOnConsecutiveCalls([$bootstrap1], [$bootstrap2]);

        $this->bootstrapLoader->loadAll($this->registry, $builder1);
        $this->bootstrapLoader->loadAll($this->registry, $builder2);

    }

    public function testLoadAllUsesCorrectRegistryMethod(): void
    {
        // Verify it uses getAllSortedByPriority instead of getAll
        $this->registry->expects($this->once())
            ->method('getAllSortedByPriority')
            ->willReturn([]);

        $this->registry->expects($this->never())
            ->method('getAll');

        $this->bootstrapLoader->loadAll($this->registry, $this->builder);
    }
}
