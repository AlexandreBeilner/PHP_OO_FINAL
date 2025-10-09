<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Http\Routing;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Http\Routing\RouteProviderManager;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use Slim\App;

final class RouteProviderManagerTest extends TestCase
{
    private RouteProviderManager $routeProviderManager;

    protected function setUp(): void
    {
        $this->routeProviderManager = new RouteProviderManager();
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $manager = new RouteProviderManager();

        $this->assertInstanceOf(RouteProviderManager::class, $manager);
    }

    public function testGetRouteProvidersReturnsEmptyArrayInitially(): void
    {
        $result = $this->routeProviderManager->getRouteProviders();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testRegisterRouteProviderAddsProvider(): void
    {
        $provider = $this->createMock(RouteProviderInterface::class);

        $this->routeProviderManager->registerRouteProvider($provider);

        $providers = $this->routeProviderManager->getRouteProviders();
        $this->assertCount(1, $providers);
        $this->assertSame($provider, $providers[0]);
    }

    public function testRegisterMultipleRouteProviders(): void
    {
        $provider1 = $this->createMock(RouteProviderInterface::class);
        $provider2 = $this->createMock(RouteProviderInterface::class);

        $this->routeProviderManager->registerRouteProvider($provider1);
        $this->routeProviderManager->registerRouteProvider($provider2);

        $providers = $this->routeProviderManager->getRouteProviders();
        $this->assertCount(2, $providers);
        $this->assertSame($provider1, $providers[0]);
        $this->assertSame($provider2, $providers[1]);
    }

    public function testRegisterRouteProvidersArrayAddsMultipleProviders(): void
    {
        $provider1 = $this->createMock(RouteProviderInterface::class);
        $provider2 = $this->createMock(RouteProviderInterface::class);
        $provider3 = $this->createMock(RouteProviderInterface::class);

        $providers = [$provider1, $provider2, $provider3];
        $this->routeProviderManager->registerRouteProviders($providers);

        $result = $this->routeProviderManager->getRouteProviders();
        $this->assertCount(3, $result);
        $this->assertSame($provider1, $result[0]);
        $this->assertSame($provider2, $result[1]);
        $this->assertSame($provider3, $result[2]);
    }

    public function testRegisterRouteProvidersWithEmptyArray(): void
    {
        $this->routeProviderManager->registerRouteProviders([]);

        $result = $this->routeProviderManager->getRouteProviders();
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetRouteProviderByModuleFindsCorrectProvider(): void
    {
        $provider1 = $this->createMock(RouteProviderInterface::class);
        $provider2 = $this->createMock(RouteProviderInterface::class);

        $provider1->expects($this->once())
            ->method('belongsToModule')
            ->with('Auth')
            ->willReturn(false);

        $provider2->expects($this->once())
            ->method('belongsToModule')
            ->with('Auth')
            ->willReturn(true);

        $this->routeProviderManager->registerRouteProvider($provider1);
        $this->routeProviderManager->registerRouteProvider($provider2);

        $result = $this->routeProviderManager->getRouteProviderByModule('Auth');

        $this->assertSame($provider2, $result);
    }

    public function testGetRouteProviderByModuleReturnsNullWhenNotFound(): void
    {
        $provider1 = $this->createMock(RouteProviderInterface::class);
        $provider2 = $this->createMock(RouteProviderInterface::class);

        $provider1->expects($this->once())
            ->method('belongsToModule')
            ->with('NonExistent')
            ->willReturn(false);

        $provider2->expects($this->once())
            ->method('belongsToModule')
            ->with('NonExistent')
            ->willReturn(false);

        $this->routeProviderManager->registerRouteProvider($provider1);
        $this->routeProviderManager->registerRouteProvider($provider2);

        $result = $this->routeProviderManager->getRouteProviderByModule('NonExistent');

        $this->assertNull($result);
    }

    public function testGetRouteProviderByModuleReturnsNullWhenEmpty(): void
    {
        $result = $this->routeProviderManager->getRouteProviderByModule('AnyModule');

        $this->assertNull($result);
    }

    public function testLoadAllRoutesCallsRegisterOnAllProviders(): void
    {
        $app = $this->createMock(App::class);
        $provider1 = $this->createMock(RouteProviderInterface::class);
        $provider2 = $this->createMock(RouteProviderInterface::class);

        // Configure priority comparison
        $provider1->method('hasPriorityOver')
            ->willReturnCallback(function($other) use ($provider2) {
                return $other === $provider2 ? true : false;
            });
        
        $provider2->method('hasPriorityOver')
            ->willReturnCallback(function($other) use ($provider1) {
                return $other === $provider1 ? false : false;
            });

        $provider1->expects($this->once())
            ->method('registerRoutes')
            ->with($app);

        $provider2->expects($this->once())
            ->method('registerRoutes')
            ->with($app);

        $this->routeProviderManager->registerRouteProvider($provider1);
        $this->routeProviderManager->registerRouteProvider($provider2);

        $this->routeProviderManager->loadAllRoutes($app);
    }

    public function testLoadAllRoutesWithEmptyProvidersDoesNothing(): void
    {        
        $app = $this->createMock(App::class);
        
        // App should not receive any calls when there are no providers
        $app->expects($this->never())->method($this->anything());

        // Should complete successfully without calling any app methods
        $result = $this->routeProviderManager->loadAllRoutes($app);
        $this->assertNull($result); // loadAllRoutes returns void
    }

    public function testLoadAllRoutesSortsProvidersByPriority(): void
    {
        $app = $this->createMock(App::class);
        $highPriorityProvider = $this->createMock(RouteProviderInterface::class);
        $lowPriorityProvider = $this->createMock(RouteProviderInterface::class);

        // High priority provider has priority over low priority provider
        $highPriorityProvider->method('hasPriorityOver')
            ->willReturnCallback(function($other) use ($lowPriorityProvider) {
                return $other === $lowPriorityProvider ? true : false;
            });
        
        $lowPriorityProvider->method('hasPriorityOver')
            ->willReturnCallback(function($other) use ($highPriorityProvider) {
                return $other === $highPriorityProvider ? false : false;
            });

        $registrationOrder = [];
        
        $highPriorityProvider->expects($this->once())
            ->method('registerRoutes')
            ->with($app)
            ->willReturnCallback(function() use (&$registrationOrder) {
                $registrationOrder[] = 'high';
            });

        $lowPriorityProvider->expects($this->once())
            ->method('registerRoutes')
            ->with($app)
            ->willReturnCallback(function() use (&$registrationOrder) {
                $registrationOrder[] = 'low';
            });

        // Register in reverse priority order
        $this->routeProviderManager->registerRouteProvider($lowPriorityProvider);
        $this->routeProviderManager->registerRouteProvider($highPriorityProvider);

        $this->routeProviderManager->loadAllRoutes($app);

        // High priority should be registered first
        $this->assertEquals(['high', 'low'], $registrationOrder);
    }

    public function testGetRouteProviderByModuleHandlesMultipleMatches(): void
    {
        $provider1 = $this->createMock(RouteProviderInterface::class);
        $provider2 = $this->createMock(RouteProviderInterface::class);

        // Both providers match the same module - should return the first one
        $provider1->expects($this->once())
            ->method('belongsToModule')
            ->with('Security')
            ->willReturn(true);

        $provider2->expects($this->never())
            ->method('belongsToModule');

        $this->routeProviderManager->registerRouteProvider($provider1);
        $this->routeProviderManager->registerRouteProvider($provider2);

        $result = $this->routeProviderManager->getRouteProviderByModule('Security');

        $this->assertSame($provider1, $result);
    }

    public function testManagerIsStateless(): void
    {
        $manager1 = new RouteProviderManager();
        $manager2 = new RouteProviderManager();

        $provider1 = $this->createMock(RouteProviderInterface::class);
        $provider2 = $this->createMock(RouteProviderInterface::class);

        $manager1->registerRouteProvider($provider1);
        $manager2->registerRouteProvider($provider2);

        $this->assertCount(1, $manager1->getRouteProviders());
        $this->assertCount(1, $manager2->getRouteProviders());
        $this->assertNotSame($manager1, $manager2);
        $this->assertSame($provider1, $manager1->getRouteProviders()[0]);
        $this->assertSame($provider2, $manager2->getRouteProviders()[0]);
    }

    public function testAllMethodsReturnExpectedTypes(): void
    {
        $provider = $this->createMock(RouteProviderInterface::class);
        $app = $this->createMock(App::class);
        
        $provider->method('belongsToModule')->willReturn(false);
        $provider->method('hasPriorityOver')->willReturn(false);
        $provider->method('registerRoutes');

        $this->assertIsArray($this->routeProviderManager->getRouteProviders());
        $this->assertNull($this->routeProviderManager->getRouteProviderByModule('NonExistent'));

        $this->routeProviderManager->registerRouteProvider($provider);
        $this->assertIsArray($this->routeProviderManager->getRouteProviders());

        // loadAllRoutes should return void (no return)
        $result = $this->routeProviderManager->loadAllRoutes($app);
        $this->assertNull($result);

        // registerRouteProviders should return void (no return)
        $result = $this->routeProviderManager->registerRouteProviders([$provider]);
        $this->assertNull($result);
    }

    public function testRegistrationOrderIsPreserved(): void
    {
        $provider1 = $this->createMock(RouteProviderInterface::class);
        $provider2 = $this->createMock(RouteProviderInterface::class);
        $provider3 = $this->createMock(RouteProviderInterface::class);

        $this->routeProviderManager->registerRouteProvider($provider1);
        $this->routeProviderManager->registerRouteProvider($provider2);
        $this->routeProviderManager->registerRouteProvider($provider3);

        $providers = $this->routeProviderManager->getRouteProviders();

        $this->assertSame($provider1, $providers[0]);
        $this->assertSame($provider2, $providers[1]);
        $this->assertSame($provider3, $providers[2]);
    }

    public function testMixedRegistrationMethods(): void
    {
        $provider1 = $this->createMock(RouteProviderInterface::class);
        $provider2 = $this->createMock(RouteProviderInterface::class);
        $provider3 = $this->createMock(RouteProviderInterface::class);
        $provider4 = $this->createMock(RouteProviderInterface::class);

        // Register using single method
        $this->routeProviderManager->registerRouteProvider($provider1);
        $this->routeProviderManager->registerRouteProvider($provider2);

        // Register using array method
        $this->routeProviderManager->registerRouteProviders([$provider3, $provider4]);

        $providers = $this->routeProviderManager->getRouteProviders();
        $this->assertCount(4, $providers);
        $this->assertSame($provider1, $providers[0]);
        $this->assertSame($provider2, $providers[1]);
        $this->assertSame($provider3, $providers[2]);
        $this->assertSame($provider4, $providers[3]);
    }

    public function testLoadAllRoutesWithSingleProvider(): void
    {
        $app = $this->createMock(App::class);
        $provider = $this->createMock(RouteProviderInterface::class);

        $provider->method('hasPriorityOver')->willReturn(false);
        $provider->expects($this->once())
            ->method('registerRoutes')
            ->with($app);

        $this->routeProviderManager->registerRouteProvider($provider);
        $this->routeProviderManager->loadAllRoutes($app);
    }

    public function testGetRouteProviderByModuleWithDifferentModuleNames(): void
    {
        $provider1 = $this->createMock(RouteProviderInterface::class);
        $provider2 = $this->createMock(RouteProviderInterface::class);

        $provider1->method('belongsToModule')
            ->willReturnMap([
                ['Auth', true],
                ['Security', false],
                ['System', false],
            ]);

        $provider2->method('belongsToModule')
            ->willReturnMap([
                ['Auth', false],
                ['Security', true],
                ['System', false],
            ]);

        $this->routeProviderManager->registerRouteProvider($provider1);
        $this->routeProviderManager->registerRouteProvider($provider2);

        $this->assertSame($provider1, $this->routeProviderManager->getRouteProviderByModule('Auth'));
        $this->assertSame($provider2, $this->routeProviderManager->getRouteProviderByModule('Security'));
        $this->assertNull($this->routeProviderManager->getRouteProviderByModule('System'));
    }

    public function testConsistentBehaviorAcrossInstances(): void
    {
        $manager1 = new RouteProviderManager();
        $manager2 = new RouteProviderManager();

        // Both managers should behave identically when empty
        $this->assertEquals($manager1->getRouteProviders(), $manager2->getRouteProviders());
        $this->assertNull($manager1->getRouteProviderByModule('Any'));
        $this->assertNull($manager2->getRouteProviderByModule('Any'));

        // Both should handle registration the same way
        $provider = $this->createMock(RouteProviderInterface::class);
        
        $manager1->registerRouteProvider($provider);
        $manager2->registerRouteProvider($provider);

        $this->assertCount(1, $manager1->getRouteProviders());
        $this->assertCount(1, $manager2->getRouteProviders());
    }

    public function testComplexPriorityScenario(): void
    {
        $app = $this->createMock(App::class);
        
        $coreProvider = $this->createMock(RouteProviderInterface::class);
        $authProvider = $this->createMock(RouteProviderInterface::class);
        $userProvider = $this->createMock(RouteProviderInterface::class);

        // Core has highest priority, then Auth, then User
        $coreProvider->method('hasPriorityOver')
            ->willReturnCallback(function($other) use ($authProvider, $userProvider) {
                return in_array($other, [$authProvider, $userProvider], true);
            });

        $authProvider->method('hasPriorityOver')
            ->willReturnCallback(function($other) use ($coreProvider, $userProvider) {
                if ($other === $coreProvider) return false;
                if ($other === $userProvider) return true;
                return false;
            });

        $userProvider->method('hasPriorityOver')
            ->willReturnCallback(function($other) use ($coreProvider, $authProvider) {
                return false; // Lowest priority
            });

        $executionOrder = [];

        $coreProvider->expects($this->once())
            ->method('registerRoutes')
            ->willReturnCallback(function() use (&$executionOrder) {
                $executionOrder[] = 'core';
            });

        $authProvider->expects($this->once())
            ->method('registerRoutes')
            ->willReturnCallback(function() use (&$executionOrder) {
                $executionOrder[] = 'auth';
            });

        $userProvider->expects($this->once())
            ->method('registerRoutes')
            ->willReturnCallback(function() use (&$executionOrder) {
                $executionOrder[] = 'user';
            });

        // Register in random order
        $this->routeProviderManager->registerRouteProvider($userProvider);
        $this->routeProviderManager->registerRouteProvider($coreProvider);
        $this->routeProviderManager->registerRouteProvider($authProvider);

        $this->routeProviderManager->loadAllRoutes($app);

        // Should execute in priority order: core, auth, user
        $this->assertEquals(['core', 'auth', 'user'], $executionOrder);
    }
}
