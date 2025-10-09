<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Http\Routing\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Http\Routing\Impl\RouteProviderFactory;
use App\Application\Shared\Http\Routing\RouteProviderFactoryInterface;
use App\Application\Shared\Http\Routing\RouteProviderInterface;

final class RouteProviderFactoryTest extends TestCase
{
    private RouteProviderFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new RouteProviderFactory();
    }

    public function testImplementsRouteProviderFactoryInterface(): void
    {
        $this->assertInstanceOf(RouteProviderFactoryInterface::class, $this->factory);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $factory = new RouteProviderFactory();

        $this->assertInstanceOf(RouteProviderFactory::class, $factory);
        $this->assertInstanceOf(RouteProviderFactoryInterface::class, $factory);
    }

    public function testCreateCoreRouteProviderReturnsRouteProviderInterface(): void
    {
        $result = $this->factory->createCoreRouteProvider();

        $this->assertInstanceOf(RouteProviderInterface::class, $result);
    }

    public function testCreateCoreRouteProviderReturnsCoreRouteProvider(): void
    {
        $result = $this->factory->createCoreRouteProvider();

        // The returned instance should be specifically CoreRouteProvider
        $this->assertEquals('App\Application\Shared\Http\Routing\Impl\CoreRouteProvider', get_class($result));
    }

    public function testCreateSystemRouteProviderReturnsRouteProviderInterface(): void
    {
        $result = $this->factory->createSystemRouteProvider();

        $this->assertInstanceOf(RouteProviderInterface::class, $result);
    }

    public function testCreateSystemRouteProviderReturnsSystemRouteProvider(): void
    {
        $result = $this->factory->createSystemRouteProvider();

        // The returned instance should be specifically SystemRouteProvider
        $this->assertEquals('App\Application\Modules\System\Http\Routing\Impl\SystemRouteProvider', get_class($result));
    }

    public function testCreateAuthRouteProviderReturnsRouteProviderInterface(): void
    {
        $result = $this->factory->createAuthRouteProvider();

        $this->assertInstanceOf(RouteProviderInterface::class, $result);
    }

    public function testCreateAuthRouteProviderReturnsAuthRouteProvider(): void
    {
        $result = $this->factory->createAuthRouteProvider();

        // The returned instance should be specifically AuthRouteProvider
        $this->assertEquals('App\Application\Modules\Auth\Http\Routing\Impl\AuthRouteProvider', get_class($result));
    }

    public function testCreateSecurityRouteProviderReturnsRouteProviderInterface(): void
    {
        $result = $this->factory->createSecurityRouteProvider();

        $this->assertInstanceOf(RouteProviderInterface::class, $result);
    }

    public function testCreateSecurityRouteProviderReturnsSecurityRouteProvider(): void
    {
        $result = $this->factory->createSecurityRouteProvider();

        // The returned instance should be specifically SecurityRouteProvider
        $this->assertEquals('App\Application\Modules\Security\Http\Routing\Impl\SecurityRouteProvider', get_class($result));
    }

    public function testAllFactoryMethodsCreateDifferentInstances(): void
    {
        $coreProvider = $this->factory->createCoreRouteProvider();
        $systemProvider = $this->factory->createSystemRouteProvider();
        $authProvider = $this->factory->createAuthRouteProvider();
        $securityProvider = $this->factory->createSecurityRouteProvider();

        // All providers should be different instances
        $this->assertNotSame($coreProvider, $systemProvider);
        $this->assertNotSame($coreProvider, $authProvider);
        $this->assertNotSame($coreProvider, $securityProvider);
        $this->assertNotSame($systemProvider, $authProvider);
        $this->assertNotSame($systemProvider, $securityProvider);
        $this->assertNotSame($authProvider, $securityProvider);
    }

    public function testFactoryIsStateless(): void
    {
        // Multiple calls to the same factory method should create independent instances
        $provider1 = $this->factory->createCoreRouteProvider();
        $provider2 = $this->factory->createCoreRouteProvider();

        $this->assertNotSame($provider1, $provider2);
        $this->assertEquals(get_class($provider1), get_class($provider2));
    }

    public function testMultipleFactoryInstancesCreateSameTypes(): void
    {
        $factory1 = new RouteProviderFactory();
        $factory2 = new RouteProviderFactory();

        $provider1 = $factory1->createCoreRouteProvider();
        $provider2 = $factory2->createCoreRouteProvider();

        $this->assertNotSame($provider1, $provider2);
        $this->assertEquals(get_class($provider1), get_class($provider2));
    }

    public function testAllCreatedProvidersImplementInterface(): void
    {
        $providers = [
            $this->factory->createCoreRouteProvider(),
            $this->factory->createSystemRouteProvider(),
            $this->factory->createAuthRouteProvider(),
            $this->factory->createSecurityRouteProvider()
        ];

        foreach ($providers as $provider) {
            $this->assertInstanceOf(RouteProviderInterface::class, $provider);
        }
    }

    public function testFactoryMethodsHaveConsistentBehavior(): void
    {
        // Test that each method consistently creates the same type
        for ($i = 0; $i < 3; $i++) {
            $coreProvider = $this->factory->createCoreRouteProvider();
            $systemProvider = $this->factory->createSystemRouteProvider();
            $authProvider = $this->factory->createAuthRouteProvider();
            $securityProvider = $this->factory->createSecurityRouteProvider();

            $this->assertEquals('App\Application\Shared\Http\Routing\Impl\CoreRouteProvider', get_class($coreProvider));
            $this->assertEquals('App\Application\Modules\System\Http\Routing\Impl\SystemRouteProvider', get_class($systemProvider));
            $this->assertEquals('App\Application\Modules\Auth\Http\Routing\Impl\AuthRouteProvider', get_class($authProvider));
            $this->assertEquals('App\Application\Modules\Security\Http\Routing\Impl\SecurityRouteProvider', get_class($securityProvider));
        }
    }

    public function testFactoryDoesNotRequireParameters(): void
    {
        // All factory methods should work without parameters
        $this->assertInstanceOf(RouteProviderInterface::class, $this->factory->createCoreRouteProvider());
        $this->assertInstanceOf(RouteProviderInterface::class, $this->factory->createSystemRouteProvider());
        $this->assertInstanceOf(RouteProviderInterface::class, $this->factory->createAuthRouteProvider());
        $this->assertInstanceOf(RouteProviderInterface::class, $this->factory->createSecurityRouteProvider());
    }

    public function testFactoryProvidesCompleteModuleCoverage(): void
    {
        // Factory should provide route providers for all main modules
        $expectedClasses = [
            'App\Application\Shared\Http\Routing\Impl\CoreRouteProvider',
            'App\Application\Modules\System\Http\Routing\Impl\SystemRouteProvider',
            'App\Application\Modules\Auth\Http\Routing\Impl\AuthRouteProvider',
            'App\Application\Modules\Security\Http\Routing\Impl\SecurityRouteProvider'
        ];

        $actualClasses = [
            get_class($this->factory->createCoreRouteProvider()),
            get_class($this->factory->createSystemRouteProvider()),
            get_class($this->factory->createAuthRouteProvider()),
            get_class($this->factory->createSecurityRouteProvider())
        ];

        $this->assertEquals($expectedClasses, $actualClasses);
    }
}
