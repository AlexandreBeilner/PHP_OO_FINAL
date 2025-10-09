<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Orchestrator;

use App\Application\Shared\EntityPaths\EntityPathCollectorInterface;
use App\Application\Shared\Loader\BootstrapLoaderInterface;
use App\Application\Shared\Loader\RouteLoaderInterface;
use App\Application\Shared\Orchestrator\LoaderBundle;
use PHPUnit\Framework\TestCase;

final class LoaderBundleTest extends TestCase
{
    public function testAllGettersReturnDifferentInterfaces(): void
    {
        $bootstrapLoader = $this->createMock(BootstrapLoaderInterface::class);
        $routeLoader = $this->createMock(RouteLoaderInterface::class);
        $entityPathCollector = $this->createMock(EntityPathCollectorInterface::class);

        $loaderBundle = new LoaderBundle($bootstrapLoader, $routeLoader, $entityPathCollector);

        $bootstrap = $loaderBundle->getBootstrapLoader();
        $route = $loaderBundle->getRouteLoader();
        $entity = $loaderBundle->getEntityPathCollector();

        // Each getter should return a different type of interface
        $this->assertNotSame($bootstrap, $route);
        $this->assertNotSame($route, $entity);
        $this->assertNotSame($bootstrap, $entity);

        // Verify they implement their respective interfaces
        $this->assertInstanceOf(BootstrapLoaderInterface::class, $bootstrap);
        $this->assertInstanceOf(RouteLoaderInterface::class, $route);
        $this->assertInstanceOf(EntityPathCollectorInterface::class, $entity);
    }

    public function testConstructorAcceptsOnlyInterfaces(): void
    {
        // This test verifies that constructor type hints work correctly
        $bootstrapLoader = $this->createMock(BootstrapLoaderInterface::class);
        $routeLoader = $this->createMock(RouteLoaderInterface::class);
        $entityPathCollector = $this->createMock(EntityPathCollectorInterface::class);

        // Should not throw any exceptions
        $loaderBundle = new LoaderBundle($bootstrapLoader, $routeLoader, $entityPathCollector);

        // Verify successful construction
        $this->assertInstanceOf(LoaderBundle::class, $loaderBundle);
    }

    public function testConstructorSetsAllLoaders(): void
    {
        $bootstrapLoader = $this->createMock(BootstrapLoaderInterface::class);
        $routeLoader = $this->createMock(RouteLoaderInterface::class);
        $entityPathCollector = $this->createMock(EntityPathCollectorInterface::class);

        $loaderBundle = new LoaderBundle($bootstrapLoader, $routeLoader, $entityPathCollector);

        $this->assertSame($bootstrapLoader, $loaderBundle->getBootstrapLoader());
        $this->assertSame($routeLoader, $loaderBundle->getRouteLoader());
        $this->assertSame($entityPathCollector, $loaderBundle->getEntityPathCollector());
    }

    public function testConstructorWithSameInstancesReusedCorrectly(): void
    {
        $bootstrapLoader = $this->createMock(BootstrapLoaderInterface::class);
        $routeLoader = $this->createMock(RouteLoaderInterface::class);
        $entityPathCollector = $this->createMock(EntityPathCollectorInterface::class);

        // Create multiple bundles with same instances
        $bundle1 = new LoaderBundle($bootstrapLoader, $routeLoader, $entityPathCollector);
        $bundle2 = new LoaderBundle($bootstrapLoader, $routeLoader, $entityPathCollector);

        // Bundles should be different objects
        $this->assertNotSame($bundle1, $bundle2);

        // But should contain same loader instances
        $this->assertSame($bundle1->getBootstrapLoader(), $bundle2->getBootstrapLoader());
        $this->assertSame($bundle1->getRouteLoader(), $bundle2->getRouteLoader());
        $this->assertSame($bundle1->getEntityPathCollector(), $bundle2->getEntityPathCollector());
    }

    public function testGetBootstrapLoaderReturnsCorrectInstance(): void
    {
        $bootstrapLoader = $this->createMock(BootstrapLoaderInterface::class);
        $routeLoader = $this->createMock(RouteLoaderInterface::class);
        $entityPathCollector = $this->createMock(EntityPathCollectorInterface::class);

        $loaderBundle = new LoaderBundle($bootstrapLoader, $routeLoader, $entityPathCollector);

        $result = $loaderBundle->getBootstrapLoader();

        $this->assertInstanceOf(BootstrapLoaderInterface::class, $result);
        $this->assertSame($bootstrapLoader, $result);
    }

    public function testGetEntityPathCollectorReturnsCorrectInstance(): void
    {
        $bootstrapLoader = $this->createMock(BootstrapLoaderInterface::class);
        $routeLoader = $this->createMock(RouteLoaderInterface::class);
        $entityPathCollector = $this->createMock(EntityPathCollectorInterface::class);

        $loaderBundle = new LoaderBundle($bootstrapLoader, $routeLoader, $entityPathCollector);

        $result = $loaderBundle->getEntityPathCollector();

        $this->assertInstanceOf(EntityPathCollectorInterface::class, $result);
        $this->assertSame($entityPathCollector, $result);
    }

    public function testGetRouteLoaderReturnsCorrectInstance(): void
    {
        $bootstrapLoader = $this->createMock(BootstrapLoaderInterface::class);
        $routeLoader = $this->createMock(RouteLoaderInterface::class);
        $entityPathCollector = $this->createMock(EntityPathCollectorInterface::class);

        $loaderBundle = new LoaderBundle($bootstrapLoader, $routeLoader, $entityPathCollector);

        $result = $loaderBundle->getRouteLoader();

        $this->assertInstanceOf(RouteLoaderInterface::class, $result);
        $this->assertSame($routeLoader, $result);
    }

    public function testGettersProvideCompleteAccess(): void
    {
        $bootstrapLoader = $this->createMock(BootstrapLoaderInterface::class);
        $routeLoader = $this->createMock(RouteLoaderInterface::class);
        $entityPathCollector = $this->createMock(EntityPathCollectorInterface::class);

        $loaderBundle = new LoaderBundle($bootstrapLoader, $routeLoader, $entityPathCollector);

        // All three dependencies should be accessible
        $this->assertNotNull($loaderBundle->getBootstrapLoader());
        $this->assertNotNull($loaderBundle->getRouteLoader());
        $this->assertNotNull($loaderBundle->getEntityPathCollector());

        // And they should be the exact instances provided
        $this->assertSame($bootstrapLoader, $loaderBundle->getBootstrapLoader());
        $this->assertSame($routeLoader, $loaderBundle->getRouteLoader());
        $this->assertSame($entityPathCollector, $loaderBundle->getEntityPathCollector());
    }

    public function testImmutabilityOfLoadersAfterConstruction(): void
    {
        $bootstrapLoader = $this->createMock(BootstrapLoaderInterface::class);
        $routeLoader = $this->createMock(RouteLoaderInterface::class);
        $entityPathCollector = $this->createMock(EntityPathCollectorInterface::class);

        $loaderBundle = new LoaderBundle($bootstrapLoader, $routeLoader, $entityPathCollector);

        // Get loaders multiple times - should always return same instances
        $firstBootstrapCall = $loaderBundle->getBootstrapLoader();
        $secondBootstrapCall = $loaderBundle->getBootstrapLoader();

        $firstRouteCall = $loaderBundle->getRouteLoader();
        $secondRouteCall = $loaderBundle->getRouteLoader();

        $firstEntityCall = $loaderBundle->getEntityPathCollector();
        $secondEntityCall = $loaderBundle->getEntityPathCollector();

        $this->assertSame($firstBootstrapCall, $secondBootstrapCall);
        $this->assertSame($firstRouteCall, $secondRouteCall);
        $this->assertSame($firstEntityCall, $secondEntityCall);
    }

    public function testValueObjectBehavior(): void
    {
        $bootstrapLoader1 = $this->createMock(BootstrapLoaderInterface::class);
        $routeLoader1 = $this->createMock(RouteLoaderInterface::class);
        $entityPathCollector1 = $this->createMock(EntityPathCollectorInterface::class);

        $bootstrapLoader2 = $this->createMock(BootstrapLoaderInterface::class);
        $routeLoader2 = $this->createMock(RouteLoaderInterface::class);
        $entityPathCollector2 = $this->createMock(EntityPathCollectorInterface::class);

        $bundle1 = new LoaderBundle($bootstrapLoader1, $routeLoader1, $entityPathCollector1);
        $bundle2 = new LoaderBundle($bootstrapLoader2, $routeLoader2, $entityPathCollector2);

        // Different instances should be different objects
        $this->assertNotSame($bundle1, $bundle2);

        // But should contain their respective dependencies
        $this->assertSame($bootstrapLoader1, $bundle1->getBootstrapLoader());
        $this->assertSame($bootstrapLoader2, $bundle2->getBootstrapLoader());
    }
}
