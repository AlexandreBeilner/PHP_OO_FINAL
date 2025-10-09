<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\EntityPaths\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\EntityPaths\Impl\EntityPathCollector;
use App\Application\Shared\EntityPaths\EntityPathCollectorInterface;
use App\Application\Shared\EntityPaths\EntityPathProviderInterface;

final class EntityPathCollectorTest extends TestCase
{
    private EntityPathCollector $collector;

    protected function setUp(): void
    {
        $this->collector = new EntityPathCollector();
    }

    public function testImplementsEntityPathCollectorInterface(): void
    {
        $this->assertInstanceOf(EntityPathCollectorInterface::class, $this->collector);
    }

    public function testConstructorInitializesEmptyState(): void
    {
        $result = $this->collector->collectAllEntityPaths();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testRegisterProviderAcceptsProvider(): void
    {
        $provider = $this->createMock(EntityPathProviderInterface::class);
        $provider->method('hasEntityPaths')->willReturn(false);

        // Should not throw exception
        $this->collector->registerProvider($provider);

        // Verify by calling collect - should still return empty array
        $result = $this->collector->collectAllEntityPaths();
        $this->assertEquals([], $result);
    }

    public function testCollectAllEntityPathsWithSingleProvider(): void
    {
        $expectedPaths = ['/path/to/entity1', '/path/to/entity2'];
        
        $provider = $this->createMock(EntityPathProviderInterface::class);
        $provider->method('hasEntityPaths')->willReturn(true);
        $provider->method('getEntityPaths')->willReturn($expectedPaths);

        $this->collector->registerProvider($provider);
        $result = $this->collector->collectAllEntityPaths();

        $this->assertEquals($expectedPaths, $result);
    }

    public function testCollectAllEntityPathsWithMultipleProviders(): void
    {
        $paths1 = ['/path/to/entity1', '/path/to/entity2'];
        $paths2 = ['/path/to/entity3', '/path/to/entity4'];
        $expectedMerged = ['/path/to/entity1', '/path/to/entity2', '/path/to/entity3', '/path/to/entity4'];

        $provider1 = $this->createMock(EntityPathProviderInterface::class);
        $provider1->method('hasEntityPaths')->willReturn(true);
        $provider1->method('getEntityPaths')->willReturn($paths1);

        $provider2 = $this->createMock(EntityPathProviderInterface::class);
        $provider2->method('hasEntityPaths')->willReturn(true);
        $provider2->method('getEntityPaths')->willReturn($paths2);

        $this->collector->registerProvider($provider1);
        $this->collector->registerProvider($provider2);
        
        $result = $this->collector->collectAllEntityPaths();

        $this->assertEquals($expectedMerged, $result);
    }

    public function testCollectAllEntityPathsSkipsProvidersWithoutPaths(): void
    {
        $pathsFromActiveProvider = ['/active/entity1', '/active/entity2'];

        $emptyProvider = $this->createMock(EntityPathProviderInterface::class);
        $emptyProvider->method('hasEntityPaths')->willReturn(false);
        // getEntityPaths should not be called for empty providers

        $activeProvider = $this->createMock(EntityPathProviderInterface::class);
        $activeProvider->method('hasEntityPaths')->willReturn(true);
        $activeProvider->method('getEntityPaths')->willReturn($pathsFromActiveProvider);

        $this->collector->registerProvider($emptyProvider);
        $this->collector->registerProvider($activeProvider);
        
        $result = $this->collector->collectAllEntityPaths();

        $this->assertEquals($pathsFromActiveProvider, $result);
    }

    public function testCollectAllEntityPathsRemovesDuplicates(): void
    {
        $pathsWithDuplicates1 = ['/entity1', '/entity2', '/entity3'];
        $pathsWithDuplicates2 = ['/entity2', '/entity3', '/entity4']; // entity2 and entity3 are duplicates

        $expectedUnique = ['/entity1', '/entity2', '/entity3', '/entity4'];

        $provider1 = $this->createMock(EntityPathProviderInterface::class);
        $provider1->method('hasEntityPaths')->willReturn(true);
        $provider1->method('getEntityPaths')->willReturn($pathsWithDuplicates1);

        $provider2 = $this->createMock(EntityPathProviderInterface::class);
        $provider2->method('hasEntityPaths')->willReturn(true);
        $provider2->method('getEntityPaths')->willReturn($pathsWithDuplicates2);

        $this->collector->registerProvider($provider1);
        $this->collector->registerProvider($provider2);
        
        $result = $this->collector->collectAllEntityPaths();

        // Result should contain unique paths only
        $this->assertCount(4, $result);
        $this->assertEquals($expectedUnique, array_values($result));
    }

    public function testCollectAllEntityPathsHandlesEmptyProviders(): void
    {
        $emptyProvider1 = $this->createMock(EntityPathProviderInterface::class);
        $emptyProvider1->method('hasEntityPaths')->willReturn(false);

        $emptyProvider2 = $this->createMock(EntityPathProviderInterface::class);
        $emptyProvider2->method('hasEntityPaths')->willReturn(false);

        $this->collector->registerProvider($emptyProvider1);
        $this->collector->registerProvider($emptyProvider2);
        
        $result = $this->collector->collectAllEntityPaths();

        $this->assertEquals([], $result);
    }

    public function testRegisterProviderCanBeCalledMultipleTimes(): void
    {
        $paths1 = ['/entity1'];
        $paths2 = ['/entity2'];
        $paths3 = ['/entity3'];

        $provider1 = $this->createMock(EntityPathProviderInterface::class);
        $provider1->method('hasEntityPaths')->willReturn(true);
        $provider1->method('getEntityPaths')->willReturn($paths1);

        $provider2 = $this->createMock(EntityPathProviderInterface::class);
        $provider2->method('hasEntityPaths')->willReturn(true);
        $provider2->method('getEntityPaths')->willReturn($paths2);

        $provider3 = $this->createMock(EntityPathProviderInterface::class);
        $provider3->method('hasEntityPaths')->willReturn(true);
        $provider3->method('getEntityPaths')->willReturn($paths3);

        $this->collector->registerProvider($provider1);
        $this->collector->registerProvider($provider2);
        $this->collector->registerProvider($provider3);
        
        $result = $this->collector->collectAllEntityPaths();

        $this->assertEquals(['/entity1', '/entity2', '/entity3'], $result);
    }

    public function testCollectAllEntityPathsIsIdempotent(): void
    {
        $paths = ['/entity1', '/entity2'];
        
        $provider = $this->createMock(EntityPathProviderInterface::class);
        $provider->method('hasEntityPaths')->willReturn(true);
        $provider->method('getEntityPaths')->willReturn($paths);

        $this->collector->registerProvider($provider);
        
        $result1 = $this->collector->collectAllEntityPaths();
        $result2 = $this->collector->collectAllEntityPaths();

        $this->assertEquals($result1, $result2);
        $this->assertEquals($paths, $result1);
        $this->assertEquals($paths, $result2);
    }

    public function testRegisterProviderVoidReturnType(): void
    {
        $provider = $this->createMock(EntityPathProviderInterface::class);
        
        $result = $this->collector->registerProvider($provider);

        $this->assertNull($result);
    }
}
