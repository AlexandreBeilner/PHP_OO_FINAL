<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Http\Routing\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Http\Routing\Impl\CoreRouteProvider;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use Slim\App;
use Slim\Interfaces\RouteInterface;

final class CoreRouteProviderTest extends TestCase
{
    private CoreRouteProvider $coreRouteProvider;

    protected function setUp(): void
    {
        $this->coreRouteProvider = new CoreRouteProvider();
    }

    public function testImplementsRouteProviderInterface(): void
    {
        $this->assertInstanceOf(RouteProviderInterface::class, $this->coreRouteProvider);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $provider = new CoreRouteProvider();

        $this->assertInstanceOf(CoreRouteProvider::class, $provider);
        $this->assertInstanceOf(RouteProviderInterface::class, $provider);
    }

    public function testGetModuleNameReturnsCore(): void
    {
        $result = $this->coreRouteProvider->getModuleName();

        $this->assertEquals('Core', $result);
    }

    public function testBelongsToModuleWithCoreModule(): void
    {
        $result = $this->coreRouteProvider->belongsToModule('Core');

        $this->assertTrue($result);
    }

    public function testBelongsToModuleWithOtherModule(): void
    {
        $result = $this->coreRouteProvider->belongsToModule('Security');

        $this->assertFalse($result);
    }

    public function testBelongsToModuleWithEmptyString(): void
    {
        $result = $this->coreRouteProvider->belongsToModule('');

        $this->assertFalse($result);
    }

    public function testBelongsToModuleWithCaseSensitivity(): void
    {
        $result = $this->coreRouteProvider->belongsToModule('core');

        $this->assertFalse($result);
    }

    public function testGetPriorityReturnsHighPriority(): void
    {
        $result = $this->coreRouteProvider->getPriority();

        $this->assertEquals(10, $result);
    }

    public function testGetRoutePrefix(): void
    {
        $result = $this->coreRouteProvider->getRoutePrefix();

        $this->assertEquals('/', $result);
    }

    public function testHasPriorityOverReturnsTrue(): void
    {
        $otherProvider = $this->createMock(RouteProviderInterface::class);

        $result = $this->coreRouteProvider->hasPriorityOver($otherProvider);

        $this->assertTrue($result);
    }

    public function testHasPriorityOverWithSameProvider(): void
    {
        $result = $this->coreRouteProvider->hasPriorityOver($this->coreRouteProvider);

        $this->assertTrue($result);
    }

    public function testRegisterRoutesCallsAppGetMethod(): void
    {
        $app = $this->createMock(App::class);
        $routeInterface = $this->createMock(RouteInterface::class);

        // Expect multiple get() calls for different routes
        $app->expects($this->exactly(3))
            ->method('get')
            ->willReturnCallback(function($path, $handler) use ($routeInterface) {
                $this->assertTrue(in_array($path, ['/', '/health', '/app-status']));
                $this->assertIsCallable($handler);
                return $routeInterface;
            });

        $this->coreRouteProvider->registerRoutes($app);
    }

    public function testRegisterRoutesAddsCorrectPaths(): void
    {
        $app = $this->createMock(App::class);
        $routeInterface = $this->createMock(RouteInterface::class);
        $capturedPaths = [];

        $app->expects($this->exactly(3))
            ->method('get')
            ->willReturnCallback(function($path, $handler) use (&$capturedPaths, $routeInterface) {
                $capturedPaths[] = $path;
                return $routeInterface;
            });

        $this->coreRouteProvider->registerRoutes($app);

        $expectedPaths = ['/', '/health', '/app-status'];
        $this->assertEquals($expectedPaths, $capturedPaths);
    }

    public function testRegisterRoutesWithCallableHandlers(): void
    {
        $app = $this->createMock(App::class);
        $routeInterface = $this->createMock(RouteInterface::class);
        $capturedHandlers = [];

        $app->expects($this->exactly(3))
            ->method('get')
            ->willReturnCallback(function($path, $handler) use (&$capturedHandlers, $routeInterface) {
                $capturedHandlers[] = $handler;
                return $routeInterface;
            });

        $this->coreRouteProvider->registerRoutes($app);

        // All handlers should be callable
        foreach ($capturedHandlers as $handler) {
            $this->assertIsCallable($handler);
        }
    }

    public function testProviderIsStateless(): void
    {
        $provider1 = new CoreRouteProvider();
        $provider2 = new CoreRouteProvider();

        $this->assertEquals($provider1->getModuleName(), $provider2->getModuleName());
        $this->assertEquals($provider1->getPriority(), $provider2->getPriority());
        $this->assertEquals($provider1->getRoutePrefix(), $provider2->getRoutePrefix());
        $this->assertEquals($provider1->belongsToModule('Core'), $provider2->belongsToModule('Core'));
    }

    public function testConsistentBehaviorAcrossMultipleInstances(): void
    {
        $provider1 = new CoreRouteProvider();
        $provider2 = new CoreRouteProvider();

        $this->assertEquals($provider1->getModuleName(), $provider2->getModuleName());
        $this->assertEquals($provider1->getPriority(), $provider2->getPriority());
        $this->assertEquals($provider1->getRoutePrefix(), $provider2->getRoutePrefix());
        $this->assertEquals($provider1->belongsToModule('Core'), $provider2->belongsToModule('Core'));
    }

    public function testRegisterRoutesCanBeCalledMultipleTimes(): void
    {
        $app = $this->createMock(App::class);
        $routeInterface = $this->createMock(RouteInterface::class);

        $app->expects($this->exactly(6)) // 3 routes × 2 calls
            ->method('get')
            ->willReturn($routeInterface);

        // Should be able to call registerRoutes multiple times
        $this->coreRouteProvider->registerRoutes($app);
        $this->coreRouteProvider->registerRoutes($app);
    }

    public function testAllMethodsReturnExpectedTypes(): void
    {
        $this->assertIsString($this->coreRouteProvider->getModuleName());
        $this->assertIsInt($this->coreRouteProvider->getPriority());
        $this->assertIsString($this->coreRouteProvider->getRoutePrefix());
        $this->assertIsBool($this->coreRouteProvider->belongsToModule('Core'));
        $this->assertIsBool($this->coreRouteProvider->hasPriorityOver($this->createMock(RouteProviderInterface::class)));
    }

    public function testModuleIdentificationConsistency(): void
    {
        // Multiple calls should be consistent
        $this->assertEquals('Core', $this->coreRouteProvider->getModuleName());
        $this->assertEquals('Core', $this->coreRouteProvider->getModuleName());
        
        $this->assertTrue($this->coreRouteProvider->belongsToModule('Core'));
        $this->assertTrue($this->coreRouteProvider->belongsToModule('Core'));
    }

    public function testPrioritySystemConsistency(): void
    {
        // Priority should be consistent
        $this->assertEquals(10, $this->coreRouteProvider->getPriority());
        $this->assertEquals(10, $this->coreRouteProvider->getPriority());
        
        // Should always have priority over others (Core has highest priority)
        $mockProvider = $this->createMock(RouteProviderInterface::class);
        $this->assertTrue($this->coreRouteProvider->hasPriorityOver($mockProvider));
    }

    public function testRegisterRoutesVoidReturnType(): void
    {
        $app = $this->createMock(App::class);
        $routeInterface = $this->createMock(RouteInterface::class);
        $app->method('get')->willReturn($routeInterface);

        $result = $this->coreRouteProvider->registerRoutes($app);

        $this->assertNull($result);
    }

    public function testHasPriorityOverWithDifferentProviders(): void
    {
        $providers = [
            $this->createMock(RouteProviderInterface::class),
            $this->createMock(RouteProviderInterface::class),
            $this->createMock(RouteProviderInterface::class)
        ];

        foreach ($providers as $provider) {
            $this->assertTrue($this->coreRouteProvider->hasPriorityOver($provider));
        }
    }

    public function testBelongsToModuleWithMultipleModules(): void
    {
        $modules = ['Core', 'Security', 'Auth', 'System', '', 'random'];
        $expected = [true, false, false, false, false, false];

        for ($i = 0; $i < count($modules); $i++) {
            $result = $this->coreRouteProvider->belongsToModule($modules[$i]);
            $this->assertEquals($expected[$i], $result, "Failed for module: {$modules[$i]}");
        }
    }

    public function testProviderImplementsAllRequiredMethods(): void
    {
        $reflectionClass = new \ReflectionClass(CoreRouteProvider::class);
        $interfaces = $reflectionClass->getInterfaces();
        
        $this->assertArrayHasKey(RouteProviderInterface::class, $interfaces);
        
        // Verify all interface methods are implemented
        $interfaceReflection = new \ReflectionClass(RouteProviderInterface::class);
        $interfaceMethods = $interfaceReflection->getMethods();
        
        foreach ($interfaceMethods as $method) {
            $this->assertTrue($reflectionClass->hasMethod($method->getName()));
        }
    }
}
