<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Loader\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Loader\Impl\RouteLoader;
use App\Application\Shared\Loader\RouteLoaderInterface;
use App\Application\Shared\Registry\BootstrapRegistryInterface;
use App\Application\Shared\BootstrapInterface;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use Slim\App;

final class RouteLoaderTest extends TestCase
{
    private RouteLoader $routeLoader;
    private BootstrapRegistryInterface $registry;
    private App $app;

    protected function setUp(): void
    {
        $this->routeLoader = new RouteLoader();
        $this->registry = $this->createMock(BootstrapRegistryInterface::class);
        $this->app = $this->createMock(App::class);
    }

    public function testImplementsRouteLoaderInterface(): void
    {
        $this->assertInstanceOf(RouteLoaderInterface::class, $this->routeLoader);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $loader = new RouteLoader();

        $this->assertInstanceOf(RouteLoader::class, $loader);
        $this->assertInstanceOf(RouteLoaderInterface::class, $loader);
    }

    public function testLoadAllRoutesVoidReturnType(): void
    {
        $this->registry->expects($this->once())
            ->method('getAll')
            ->willReturn([]);

        $result = $this->routeLoader->loadAllRoutes($this->registry, $this->app);

        $this->assertNull($result);
    }

    public function testLoadAllRoutesWithEmptyBootstraps(): void
    {
        $this->registry->expects($this->once())
            ->method('getAll')
            ->willReturn([]);

        // Should not throw exception
        $this->routeLoader->loadAllRoutes($this->registry, $this->app);

    }

    public function testLoadAllRoutesWithBootstrapWithoutRouteProvider(): void
    {
        $bootstrap = $this->createMock(BootstrapInterface::class);
        $bootstrap->expects($this->once())
            ->method('getRouteProvider')
            ->willReturn(null);

        $this->registry->expects($this->once())
            ->method('getAll')
            ->willReturn([$bootstrap]);

        // Should not throw exception
        $this->routeLoader->loadAllRoutes($this->registry, $this->app);

    }

    public function testLoadAllRoutesWithBootstrapWithRouteProvider(): void
    {
        $routeProvider = $this->createMock(RouteProviderInterface::class);
        $bootstrap = $this->createMock(BootstrapInterface::class);
        
        $bootstrap->expects($this->once())
            ->method('getRouteProvider')
            ->willReturn($routeProvider);

        $this->registry->expects($this->once())
            ->method('getAll')
            ->willReturn([$bootstrap]);

        // Should not throw exception
        $this->routeLoader->loadAllRoutes($this->registry, $this->app);

    }

    public function testLoadAllRoutesWithMultipleBootstraps(): void
    {
        $routeProvider1 = $this->createMock(RouteProviderInterface::class);
        $routeProvider2 = $this->createMock(RouteProviderInterface::class);

        $bootstrap1 = $this->createMock(BootstrapInterface::class);
        $bootstrap1->expects($this->once())
            ->method('getRouteProvider')
            ->willReturn($routeProvider1);

        $bootstrap2 = $this->createMock(BootstrapInterface::class);
        $bootstrap2->expects($this->once())
            ->method('getRouteProvider')
            ->willReturn($routeProvider2);

        $bootstrap3 = $this->createMock(BootstrapInterface::class);
        $bootstrap3->expects($this->once())
            ->method('getRouteProvider')
            ->willReturn(null);

        $this->registry->expects($this->once())
            ->method('getAll')
            ->willReturn([$bootstrap1, $bootstrap2, $bootstrap3]);

        // Should not throw exception
        $this->routeLoader->loadAllRoutes($this->registry, $this->app);

    }

    public function testLoadAllRoutesCallsGetAllOnRegistry(): void
    {
        $this->registry->expects($this->once())
            ->method('getAll')
            ->willReturn([]);

        $this->routeLoader->loadAllRoutes($this->registry, $this->app);
    }

    public function testLoadAllRoutesProcessesAllBootstraps(): void
    {
        $bootstrap1 = $this->createMock(BootstrapInterface::class);
        $bootstrap2 = $this->createMock(BootstrapInterface::class);

        // Both bootstraps should have getRouteProvider called
        $bootstrap1->expects($this->once())->method('getRouteProvider')->willReturn(null);
        $bootstrap2->expects($this->once())->method('getRouteProvider')->willReturn(null);

        $this->registry->expects($this->once())
            ->method('getAll')
            ->willReturn([$bootstrap1, $bootstrap2]);

        $this->routeLoader->loadAllRoutes($this->registry, $this->app);
    }

    public function testLoadAllRoutesWithSameAppAndRegistryMultipleTimes(): void
    {
        $this->registry->expects($this->exactly(2))
            ->method('getAll')
            ->willReturn([]);

        // Should be able to call multiple times without issues
        $this->routeLoader->loadAllRoutes($this->registry, $this->app);
        $this->routeLoader->loadAllRoutes($this->registry, $this->app);

    }

    public function testLoadAllRoutesStatelessBehavior(): void
    {
        $loader1 = new RouteLoader();
        $loader2 = new RouteLoader();

        $this->registry->expects($this->exactly(2))
            ->method('getAll')
            ->willReturn([]);

        // Different instances should behave identically
        $loader1->loadAllRoutes($this->registry, $this->app);
        $loader2->loadAllRoutes($this->registry, $this->app);

    }

    public function testLoadAllRoutesHandlesRegistryReturnValue(): void
    {
        // Test that the method properly handles the array returned by registry
        $bootstraps = [
            $this->createMock(BootstrapInterface::class),
            $this->createMock(BootstrapInterface::class)
        ];

        foreach ($bootstraps as $bootstrap) {
            $bootstrap->method('getRouteProvider')->willReturn(null);
        }

        $this->registry->expects($this->once())
            ->method('getAll')
            ->willReturn($bootstraps);

        $this->routeLoader->loadAllRoutes($this->registry, $this->app);

    }
}
