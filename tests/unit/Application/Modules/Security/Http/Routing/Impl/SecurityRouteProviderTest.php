<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Modules\Security\Http\Routing\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Modules\Security\Http\Routing\Impl\SecurityRouteProvider;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use Slim\App;
use Slim\Interfaces\RouteGroupInterface;

final class SecurityRouteProviderTest extends TestCase
{
    private SecurityRouteProvider $routeProvider;
    private App $app;
    private RouteGroupInterface $group;

    protected function setUp(): void
    {
        $this->app = $this->createMock(App::class);
        $this->group = $this->createMock(RouteGroupInterface::class);
        $this->routeProvider = new SecurityRouteProvider();
    }

    public function testImplementsExpectedInterface(): void
    {
        $this->assertInstanceOf(RouteProviderInterface::class, $this->routeProvider);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $instance = new SecurityRouteProvider();

        $this->assertInstanceOf(SecurityRouteProvider::class, $instance);
        $this->assertInstanceOf(RouteProviderInterface::class, $instance);
    }

    public function testBelongsToModuleReturnsCorrectValue(): void
    {
        $this->assertTrue($this->routeProvider->belongsToModule('Security'));
        $this->assertFalse($this->routeProvider->belongsToModule('Auth'));
        $this->assertFalse($this->routeProvider->belongsToModule('System'));
        $this->assertFalse($this->routeProvider->belongsToModule('NonExistent'));
    }

    public function testBelongsToModuleWithDifferentCases(): void
    {
        $this->assertTrue($this->routeProvider->belongsToModule('Security'));
        $this->assertFalse($this->routeProvider->belongsToModule('security')); // Case sensitive
        $this->assertFalse($this->routeProvider->belongsToModule('SECURITY'));
        $this->assertFalse($this->routeProvider->belongsToModule('Security '));
    }

    public function testGetModuleNameReturnsCorrectValue(): void
    {
        $this->assertEquals('Security', $this->routeProvider->getModuleName());
    }

    public function testGetModuleNameIsConsistent(): void
    {
        $name1 = $this->routeProvider->getModuleName();
        $name2 = $this->routeProvider->getModuleName();

        $this->assertEquals($name1, $name2);
        $this->assertEquals('Security', $name1);
    }

    public function testGetPriorityReturnsCorrectValue(): void
    {
        $this->assertEquals(30, $this->routeProvider->getPriority());
    }

    public function testGetPriorityIsConsistent(): void
    {
        $priority1 = $this->routeProvider->getPriority();
        $priority2 = $this->routeProvider->getPriority();

        $this->assertEquals($priority1, $priority2);
        $this->assertEquals(30, $priority1);
    }

    public function testGetRoutePrefixReturnsCorrectValue(): void
    {
        $this->assertEquals('/api/security', $this->routeProvider->getRoutePrefix());
    }

    public function testGetRoutePrefixIsConsistent(): void
    {
        $prefix1 = $this->routeProvider->getRoutePrefix();
        $prefix2 = $this->routeProvider->getRoutePrefix();

        $this->assertEquals($prefix1, $prefix2);
        $this->assertEquals('/api/security', $prefix1);
    }

    public function testHasPriorityOverReturnsFalse(): void
    {
        $otherProvider = $this->createMock(RouteProviderInterface::class);
        
        $result = $this->routeProvider->hasPriorityOver($otherProvider);
        
        $this->assertFalse($result);
    }

    public function testHasPriorityOverWithDifferentProviders(): void
    {
        $providers = [
            $this->createMock(RouteProviderInterface::class),
            $this->createMock(RouteProviderInterface::class),
            $this->createMock(RouteProviderInterface::class)
        ];

        foreach ($providers as $provider) {
            $this->assertFalse($this->routeProvider->hasPriorityOver($provider));
        }
    }

    public function testRegisterRoutesCallsAppGroup(): void
    {
        $this->app
            ->expects($this->once())
            ->method('group')
            ->with('/api/security', $this->isType('callable'));

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
                ->with('/api/security', $this->isType('callable'));

            $this->routeProvider->registerRoutes($app);
        }
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(SecurityRouteProvider::class);
        
        $this->assertTrue($reflection->isFinal());
    }

    public function testAllMethodsReturnExpectedTypes(): void
    {
        $this->assertIsString($this->routeProvider->getModuleName());
        $this->assertIsInt($this->routeProvider->getPriority());
        $this->assertIsString($this->routeProvider->getRoutePrefix());
        $this->assertIsBool($this->routeProvider->belongsToModule('Security'));
        
        $otherProvider = $this->createMock(RouteProviderInterface::class);
        $this->assertIsBool($this->routeProvider->hasPriorityOver($otherProvider));
    }

    public function testModuleNameMatchesBelongsToModule(): void
    {
        $moduleName = $this->routeProvider->getModuleName();
        $belongsToModule = $this->routeProvider->belongsToModule($moduleName);
        
        $this->assertTrue($belongsToModule);
        $this->assertEquals('Security', $moduleName);
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

    public function testRouteProviderBehaviorConsistency(): void
    {
        // Test that provider produces consistent results
        for ($i = 0; $i < 3; $i++) {
            $this->assertEquals('Security', $this->routeProvider->getModuleName());
            $this->assertEquals(30, $this->routeProvider->getPriority());
            $this->assertEquals('/api/security', $this->routeProvider->getRoutePrefix());
            $this->assertTrue($this->routeProvider->belongsToModule('Security'));
        }
    }

    public function testMultipleInstancesHaveSameBehavior(): void
    {
        $provider1 = new SecurityRouteProvider();
        $provider2 = new SecurityRouteProvider();

        $this->assertEquals($provider1->getModuleName(), $provider2->getModuleName());
        $this->assertEquals($provider1->getPriority(), $provider2->getPriority());
        $this->assertEquals($provider1->getRoutePrefix(), $provider2->getRoutePrefix());
        
        $this->assertEquals(
            $provider1->belongsToModule('Security'),
            $provider2->belongsToModule('Security')
        );
    }

    public function testImplementsAllRequiredInterfaceMethods(): void
    {
        $interfaceReflection = new \ReflectionClass(RouteProviderInterface::class);
        $classReflection = new \ReflectionClass(SecurityRouteProvider::class);
        
        foreach ($interfaceReflection->getMethods() as $method) {
            $this->assertTrue(
                $classReflection->hasMethod($method->getName()),
                "Method {$method->getName()} should be implemented"
            );
        }
    }

    public function testConstructorRequiresNoDependencies(): void
    {
        $classReflection = new \ReflectionClass(SecurityRouteProvider::class);
        
        if ($classReflection->hasMethod('__construct')) {
            $constructor = $classReflection->getMethod('__construct');
            $parameters = $constructor->getParameters();
            $this->assertCount(0, $parameters);
        } else {
            // No explicit constructor, uses default PHP constructor with no parameters
            $this->assertTrue(true, 'Uses default constructor with no dependencies');
        }
    }
}
