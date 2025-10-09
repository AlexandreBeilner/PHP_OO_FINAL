<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Modules\Auth\Http\Routing\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Modules\Auth\Http\Routing\Impl\AuthRouteProvider;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use App\Application\Shared\Http\Routing\Impl\CoreRouteProvider;
use App\Application\Modules\System\Http\Routing\Impl\SystemRouteProvider;
use Slim\App;
use Slim\Interfaces\RouteGroupInterface;

final class AuthRouteProviderTest extends TestCase
{
    private AuthRouteProvider $routeProvider;
    private App $app;
    private RouteGroupInterface $group;

    protected function setUp(): void
    {
        $this->app = $this->createMock(App::class);
        $this->group = $this->createMock(RouteGroupInterface::class);
        $this->routeProvider = new AuthRouteProvider();
    }

    public function testImplementsExpectedInterface(): void
    {
        $this->assertInstanceOf(RouteProviderInterface::class, $this->routeProvider);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $instance = new AuthRouteProvider();

        $this->assertInstanceOf(AuthRouteProvider::class, $instance);
        $this->assertInstanceOf(RouteProviderInterface::class, $instance);
    }

    public function testBelongsToModuleReturnsCorrectValue(): void
    {
        $this->assertTrue($this->routeProvider->belongsToModule('Auth'));
        $this->assertFalse($this->routeProvider->belongsToModule('Security'));
        $this->assertFalse($this->routeProvider->belongsToModule('System'));
        $this->assertFalse($this->routeProvider->belongsToModule('NonExistent'));
    }

    public function testBelongsToModuleWithDifferentCases(): void
    {
        $this->assertTrue($this->routeProvider->belongsToModule('Auth'));
        $this->assertFalse($this->routeProvider->belongsToModule('auth')); // Case sensitive
        $this->assertFalse($this->routeProvider->belongsToModule('AUTH'));
        $this->assertFalse($this->routeProvider->belongsToModule('Auth '));
    }

    public function testGetModuleNameReturnsCorrectValue(): void
    {
        $this->assertEquals('Auth', $this->routeProvider->getModuleName());
    }

    public function testGetModuleNameIsConsistent(): void
    {
        $name1 = $this->routeProvider->getModuleName();
        $name2 = $this->routeProvider->getModuleName();

        $this->assertEquals($name1, $name2);
        $this->assertEquals('Auth', $name1);
    }

    public function testGetPriorityReturnsCorrectValue(): void
    {
        $this->assertEquals(25, $this->routeProvider->getPriority());
    }

    public function testGetPriorityIsConsistent(): void
    {
        $priority1 = $this->routeProvider->getPriority();
        $priority2 = $this->routeProvider->getPriority();

        $this->assertEquals($priority1, $priority2);
        $this->assertEquals(25, $priority1);
    }

    public function testGetRoutePrefixReturnsCorrectValue(): void
    {
        $this->assertEquals('/api/auth', $this->routeProvider->getRoutePrefix());
    }

    public function testGetRoutePrefixIsConsistent(): void
    {
        $prefix1 = $this->routeProvider->getRoutePrefix();
        $prefix2 = $this->routeProvider->getRoutePrefix();

        $this->assertEquals($prefix1, $prefix2);
        $this->assertEquals('/api/auth', $prefix1);
    }

    public function testHasPriorityOverCoreRouteProviderReturnsFalse(): void
    {
        $coreProvider = new CoreRouteProvider();
        
        $result = $this->routeProvider->hasPriorityOver($coreProvider);
        
        $this->assertFalse($result);
    }

    public function testHasPriorityOverSystemRouteProviderReturnsFalse(): void
    {
        $systemProvider = new SystemRouteProvider();
        
        $result = $this->routeProvider->hasPriorityOver($systemProvider);
        
        $this->assertFalse($result);
    }

    public function testHasPriorityOverOtherProvidersReturnsTrue(): void
    {
        $otherProvider = $this->createMock(RouteProviderInterface::class);
        
        $result = $this->routeProvider->hasPriorityOver($otherProvider);
        
        $this->assertTrue($result);
    }

    public function testHasPriorityOverWithDifferentProviders(): void
    {
        // Should return false for Core and System
        $coreProvider = new CoreRouteProvider();
        $systemProvider = new SystemRouteProvider();
        
        $this->assertFalse($this->routeProvider->hasPriorityOver($coreProvider));
        $this->assertFalse($this->routeProvider->hasPriorityOver($systemProvider));
        
        // Should return true for other providers
        $otherProvider = $this->createMock(RouteProviderInterface::class);
        $this->assertTrue($this->routeProvider->hasPriorityOver($otherProvider));
    }

    public function testRegisterRoutesCallsAppGroup(): void
    {
        $this->app
            ->expects($this->once())
            ->method('group')
            ->with('/api/auth', $this->isType('callable'));

        $this->routeProvider->registerRoutes($this->app);
    }

    public function testRegisterRoutesWithDifferentApps(): void
    {
        $apps = [
            $this->createMock(App::class),
            $this->createMock(App::class),
            $this->createMock(App::class)
        ];

        foreach ($apps as $app) {
            $app
                ->expects($this->once())
                ->method('group')
                ->with('/api/auth', $this->isType('callable'));

            $this->routeProvider->registerRoutes($app);
        }
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(AuthRouteProvider::class);
        
        $this->assertTrue($reflection->isFinal());
    }

    public function testAllMethodsReturnExpectedTypes(): void
    {
        $this->assertIsString($this->routeProvider->getModuleName());
        $this->assertIsInt($this->routeProvider->getPriority());
        $this->assertIsString($this->routeProvider->getRoutePrefix());
        $this->assertIsBool($this->routeProvider->belongsToModule('Auth'));
        
        $otherProvider = $this->createMock(RouteProviderInterface::class);
        $this->assertIsBool($this->routeProvider->hasPriorityOver($otherProvider));
    }

    public function testModuleNameMatchesBelongsToModule(): void
    {
        $moduleName = $this->routeProvider->getModuleName();
        $belongsToModule = $this->routeProvider->belongsToModule($moduleName);
        
        $this->assertTrue($belongsToModule);
        $this->assertEquals('Auth', $moduleName);
    }

    public function testRoutePrefixContainsModuleName(): void
    {
        $prefix = $this->routeProvider->getRoutePrefix();
        $moduleName = strtolower($this->routeProvider->getModuleName());
        
        $this->assertStringContainsString($moduleName, $prefix);
        $this->assertStringStartsWith('/api/', $prefix);
    }

    public function testPriorityIsPositiveInteger(): void
    {
        $priority = $this->routeProvider->getPriority();
        
        $this->assertIsInt($priority);
        $this->assertGreaterThan(0, $priority);
    }

    public function testPriorityIsBetweenSystemAndSecurity(): void
    {
        $priority = $this->routeProvider->getPriority();
        
        // Auth priority (25) should be between System (20) and Security (30)
        $this->assertGreaterThan(20, $priority); // Higher than System
        $this->assertLessThan(30, $priority);    // Lower than Security
    }

    public function testRouteProviderBehaviorConsistency(): void
    {
        // Test that provider produces consistent results
        for ($i = 0; $i < 3; $i++) {
            $this->assertEquals('Auth', $this->routeProvider->getModuleName());
            $this->assertEquals(25, $this->routeProvider->getPriority());
            $this->assertEquals('/api/auth', $this->routeProvider->getRoutePrefix());
            $this->assertTrue($this->routeProvider->belongsToModule('Auth'));
        }
    }

    public function testMultipleInstancesHaveSameBehavior(): void
    {
        $provider1 = new AuthRouteProvider();
        $provider2 = new AuthRouteProvider();

        $this->assertEquals($provider1->getModuleName(), $provider2->getModuleName());
        $this->assertEquals($provider1->getPriority(), $provider2->getPriority());
        $this->assertEquals($provider1->getRoutePrefix(), $provider2->getRoutePrefix());
        
        $this->assertEquals(
            $provider1->belongsToModule('Auth'),
            $provider2->belongsToModule('Auth')
        );
    }

    public function testImplementsAllRequiredInterfaceMethods(): void
    {
        $interfaceReflection = new \ReflectionClass(RouteProviderInterface::class);
        $classReflection = new \ReflectionClass(AuthRouteProvider::class);
        
        foreach ($interfaceReflection->getMethods() as $method) {
            $this->assertTrue(
                $classReflection->hasMethod($method->getName()),
                "Method {$method->getName()} should be implemented"
            );
        }
    }

    public function testConstructorRequiresNoDependencies(): void
    {
        $classReflection = new \ReflectionClass(AuthRouteProvider::class);
        
        if ($classReflection->hasMethod('__construct')) {
            $constructor = $classReflection->getMethod('__construct');
            $parameters = $constructor->getParameters();
            $this->assertCount(0, $parameters);
        } else {
            // No explicit constructor, uses default PHP constructor with no parameters
            $this->assertTrue(true, 'Uses default constructor with no dependencies');
        }
    }

    public function testPriorityLogicWithRealInstances(): void
    {
        $coreProvider = new CoreRouteProvider();
        $systemProvider = new SystemRouteProvider();
        $genericProvider = $this->createMock(RouteProviderInterface::class);

        // Test priority logic
        $this->assertFalse($this->routeProvider->hasPriorityOver($coreProvider), 
            'Auth should NOT have priority over Core');
        $this->assertFalse($this->routeProvider->hasPriorityOver($systemProvider), 
            'Auth should NOT have priority over System');
        $this->assertTrue($this->routeProvider->hasPriorityOver($genericProvider), 
            'Auth should have priority over generic providers');
    }
}
