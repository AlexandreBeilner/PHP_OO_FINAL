<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Impl\CommonBootstrap;
use App\Application\Shared\BootstrapInterface;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use App\Application\Shared\Http\Routing\RouteProviderFactoryInterface;
use DI\ContainerBuilder;

final class CommonBootstrapTest extends TestCase
{
    private CommonBootstrap $commonBootstrap;
    private ContainerBuilder $builder;

    protected function setUp(): void
    {
        $this->commonBootstrap = new CommonBootstrap();
        $this->builder = $this->createMock(ContainerBuilder::class);
    }

    public function testImplementsBootstrapInterface(): void
    {
        $this->assertInstanceOf(BootstrapInterface::class, $this->commonBootstrap);
    }

    public function testConstructorWithoutRouteProviderFactory(): void
    {
        $bootstrap = new CommonBootstrap();

        $this->assertInstanceOf(CommonBootstrap::class, $bootstrap);
        $this->assertInstanceOf(BootstrapInterface::class, $bootstrap);
    }

    public function testConstructorWithRouteProviderFactory(): void
    {
        $factory = $this->createMock(RouteProviderFactoryInterface::class);
        $bootstrap = new CommonBootstrap($factory);

        $this->assertInstanceOf(CommonBootstrap::class, $bootstrap);
        $this->assertInstanceOf(BootstrapInterface::class, $bootstrap);
    }

    public function testRegisterVoidReturnType(): void
    {
        $this->builder->method('addDefinitions')->willReturnSelf();

        $result = $this->commonBootstrap->register($this->builder);

        $this->assertNull($result);
    }

    public function testRegisterCallsLoadServiceDefinitions(): void
    {
        // The register method should call loadServiceDefinitions internally
        // Since loadServiceDefinitions is protected, we can't mock it directly
        // But we can verify that it doesn't throw exceptions
        
        $this->builder->expects($this->atLeastOnce())
            ->method('addDefinitions')
            ->willReturnSelf();

        $this->commonBootstrap->register($this->builder);


    }

    public function testGetModuleNameReturnsCommon(): void
    {
        $result = $this->commonBootstrap->getModuleName();

        $this->assertEquals('Common', $result);
    }

    public function testBelongsToModuleWithCommonModule(): void
    {
        $result = $this->commonBootstrap->belongsToModule('Common');

        $this->assertTrue($result);
    }

    public function testBelongsToModuleWithOtherModule(): void
    {
        $result = $this->commonBootstrap->belongsToModule('Security');

        $this->assertFalse($result);
    }

    public function testBelongsToModuleWithEmptyString(): void
    {
        $result = $this->commonBootstrap->belongsToModule('');

        $this->assertFalse($result);
    }

    public function testBelongsToModuleWithCaseSensitivity(): void
    {
        $result = $this->commonBootstrap->belongsToModule('common');

        $this->assertFalse($result);
    }

    public function testGetPriorityReturnsHighPriority(): void
    {
        $result = $this->commonBootstrap->getPriority();

        $this->assertEquals(10, $result);
    }

    public function testHasPriorityOverReturnsTrue(): void
    {
        $otherBootstrap = $this->createMock(BootstrapInterface::class);

        $result = $this->commonBootstrap->hasPriorityOver($otherBootstrap);

        $this->assertTrue($result);
    }

    public function testHasPriorityOverWithSameBootstrap(): void
    {
        $result = $this->commonBootstrap->hasPriorityOver($this->commonBootstrap);

        $this->assertTrue($result);
    }

    public function testHasRoutesReturnsTrue(): void
    {
        $result = $this->commonBootstrap->hasRoutes();

        $this->assertTrue($result);
    }

    public function testGetRouteProviderWithoutFactory(): void
    {
        $bootstrap = new CommonBootstrap();

        $result = $bootstrap->getRouteProvider();

        $this->assertInstanceOf(RouteProviderInterface::class, $result);
    }

    public function testGetRouteProviderWithFactory(): void
    {
        $mockRouteProvider = $this->createMock(RouteProviderInterface::class);
        $factory = $this->createMock(RouteProviderFactoryInterface::class);
        $factory->expects($this->once())
            ->method('createCoreRouteProvider')
            ->willReturn($mockRouteProvider);

        $bootstrap = new CommonBootstrap($factory);

        $result = $bootstrap->getRouteProvider();

        $this->assertSame($mockRouteProvider, $result);
    }

    public function testGetRouteProviderWithFactoryNull(): void
    {
        $bootstrap = new CommonBootstrap(null);

        $result = $bootstrap->getRouteProvider();

        // Should create CoreRouteProvider as fallback
        $this->assertInstanceOf(RouteProviderInterface::class, $result);
    }

    public function testConsistentBehaviorAcrossMultipleInstances(): void
    {
        $bootstrap1 = new CommonBootstrap();
        $bootstrap2 = new CommonBootstrap();

        $this->assertEquals($bootstrap1->getModuleName(), $bootstrap2->getModuleName());
        $this->assertEquals($bootstrap1->getPriority(), $bootstrap2->getPriority());
        $this->assertEquals($bootstrap1->hasRoutes(), $bootstrap2->hasRoutes());
        $this->assertEquals($bootstrap1->belongsToModule('Common'), $bootstrap2->belongsToModule('Common'));
    }

    public function testRegisterCanBeCalledMultipleTimes(): void
    {
        $this->builder->expects($this->atLeastOnce())
            ->method('addDefinitions')
            ->willReturnSelf();

        // Should be able to call register multiple times without issues
        $this->commonBootstrap->register($this->builder);
        $this->commonBootstrap->register($this->builder);


    }

    public function testGetRouteProviderConsistency(): void
    {
        $factory = $this->createMock(RouteProviderFactoryInterface::class);
        $mockRouteProvider = $this->createMock(RouteProviderInterface::class);
        
        $factory->expects($this->exactly(2))
            ->method('createCoreRouteProvider')
            ->willReturn($mockRouteProvider);

        $bootstrap = new CommonBootstrap($factory);

        // Multiple calls should use the factory consistently
        $result1 = $bootstrap->getRouteProvider();
        $result2 = $bootstrap->getRouteProvider();

        $this->assertSame($mockRouteProvider, $result1);
        $this->assertSame($mockRouteProvider, $result2);
    }
}
