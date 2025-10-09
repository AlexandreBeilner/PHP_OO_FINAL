<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Impl\ApplicationBootstrap;
use App\Application\Shared\BootstrapInterface;
use DI\ContainerBuilder;

final class ApplicationBootstrapTest extends TestCase
{
    private ApplicationBootstrap $applicationBootstrap;
    private ContainerBuilder $builder;

    protected function setUp(): void
    {
        $this->applicationBootstrap = new ApplicationBootstrap();
        $this->builder = $this->createMock(ContainerBuilder::class);
    }

    public function testImplementsBootstrapInterface(): void
    {
        $this->assertInstanceOf(BootstrapInterface::class, $this->applicationBootstrap);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $bootstrap = new ApplicationBootstrap();

        $this->assertInstanceOf(ApplicationBootstrap::class, $bootstrap);
        $this->assertInstanceOf(BootstrapInterface::class, $bootstrap);
    }

    public function testRegisterVoidReturnType(): void
    {
        $result = $this->applicationBootstrap->register($this->builder);

        $this->assertNull($result);
    }

    public function testRegisterDoesNotCallBuilderMethods(): void
    {
        // ApplicationBootstrap register method does nothing,
        // so it should not call any methods on the builder
        $this->builder->expects($this->never())
            ->method($this->anything());

        $this->applicationBootstrap->register($this->builder);
    }

    public function testGetModuleNameReturnsApplication(): void
    {
        $result = $this->applicationBootstrap->getModuleName();

        $this->assertEquals('Application', $result);
    }

    public function testBelongsToModuleWithApplicationModule(): void
    {
        $result = $this->applicationBootstrap->belongsToModule('Application');

        $this->assertTrue($result);
    }

    public function testBelongsToModuleWithOtherModule(): void
    {
        $result = $this->applicationBootstrap->belongsToModule('Common');

        $this->assertFalse($result);
    }

    public function testBelongsToModuleWithEmptyString(): void
    {
        $result = $this->applicationBootstrap->belongsToModule('');

        $this->assertFalse($result);
    }

    public function testBelongsToModuleWithCaseSensitivity(): void
    {
        $result = $this->applicationBootstrap->belongsToModule('application');

        $this->assertFalse($result);
    }

    public function testGetPriorityReturnsMediumPriority(): void
    {
        $result = $this->applicationBootstrap->getPriority();

        $this->assertEquals(50, $result);
    }

    public function testHasPriorityOverReturnsFalse(): void
    {
        $otherBootstrap = $this->createMock(BootstrapInterface::class);

        $result = $this->applicationBootstrap->hasPriorityOver($otherBootstrap);

        $this->assertFalse($result);
    }

    public function testHasPriorityOverWithSameBootstrap(): void
    {
        $result = $this->applicationBootstrap->hasPriorityOver($this->applicationBootstrap);

        $this->assertFalse($result);
    }

    public function testHasPriorityOverWithDifferentBootstrapTypes(): void
    {
        $bootstrap1 = $this->createMock(BootstrapInterface::class);
        $bootstrap2 = $this->createMock(BootstrapInterface::class);
        $bootstrap3 = $this->createMock(BootstrapInterface::class);

        // ApplicationBootstrap should return false for all others
        $this->assertFalse($this->applicationBootstrap->hasPriorityOver($bootstrap1));
        $this->assertFalse($this->applicationBootstrap->hasPriorityOver($bootstrap2));
        $this->assertFalse($this->applicationBootstrap->hasPriorityOver($bootstrap3));
    }

    public function testConsistentBehaviorAcrossMultipleInstances(): void
    {
        $bootstrap1 = new ApplicationBootstrap();
        $bootstrap2 = new ApplicationBootstrap();

        $this->assertEquals($bootstrap1->getModuleName(), $bootstrap2->getModuleName());
        $this->assertEquals($bootstrap1->getPriority(), $bootstrap2->getPriority());
        $this->assertEquals($bootstrap1->belongsToModule('Application'), $bootstrap2->belongsToModule('Application'));
        $this->assertEquals($bootstrap1->hasPriorityOver($this->createMock(BootstrapInterface::class)), 
                           $bootstrap2->hasPriorityOver($this->createMock(BootstrapInterface::class)));
    }

    public function testRegisterCanBeCalledMultipleTimes(): void
    {
        // Register should be idempotent - calling multiple times should work
        $this->builder->expects($this->never())->method('addDefinitions');
        
        // Since ApplicationBootstrap register does nothing, calling multiple times should be safe
        $result1 = $this->applicationBootstrap->register($this->builder);
        $result2 = $this->applicationBootstrap->register($this->builder);
        $result3 = $this->applicationBootstrap->register($this->builder);
        
        // All calls should return the same result (void/null)
        $this->assertNull($result1);
        $this->assertNull($result2);
        $this->assertNull($result3);
    }

    public function testRegisterWithDifferentBuilders(): void
    {
        $builder1 = $this->createMock(ContainerBuilder::class);
        $builder2 = $this->createMock(ContainerBuilder::class);
        $builder3 = $this->createMock(ContainerBuilder::class);

        // All builders should not be called since register does nothing
        $builder1->expects($this->never())->method($this->anything());
        $builder2->expects($this->never())->method($this->anything());
        $builder3->expects($this->never())->method($this->anything());

        $this->applicationBootstrap->register($builder1);
        $this->applicationBootstrap->register($builder2);
        $this->applicationBootstrap->register($builder3);
    }

    public function testBootstrapIsStateless(): void
    {
        $bootstrap1 = new ApplicationBootstrap();
        $bootstrap2 = new ApplicationBootstrap();

        // Both instances should behave identically
        $this->assertEquals($bootstrap1->getModuleName(), $bootstrap2->getModuleName());
        $this->assertEquals($bootstrap1->getPriority(), $bootstrap2->getPriority());
        $this->assertEquals($bootstrap1->belongsToModule('Application'), $bootstrap2->belongsToModule('Application'));
        $this->assertEquals($bootstrap1->belongsToModule('Other'), $bootstrap2->belongsToModule('Other'));
    }

    public function testInheritsFromAbstractBootstrap(): void
    {
        // ApplicationBootstrap extends AbstractBootstrap
        $reflection = new \ReflectionClass(ApplicationBootstrap::class);
        $parent = $reflection->getParentClass();
        
        $this->assertNotFalse($parent, "ApplicationBootstrap should have a parent class");
        $this->assertEquals('App\Application\Shared\Impl\AbstractBootstrap', $parent->getName());
    }

    public function testOverridesAbstractBootstrapMethods(): void
    {
        // Verify that key methods are overridden from AbstractBootstrap
        $reflection = new \ReflectionClass(ApplicationBootstrap::class);
        
        $this->assertTrue($reflection->hasMethod('register'));
        $this->assertTrue($reflection->hasMethod('getModuleName'));
        $this->assertTrue($reflection->hasMethod('belongsToModule'));
        $this->assertTrue($reflection->hasMethod('getPriority'));
        $this->assertTrue($reflection->hasMethod('hasPriorityOver'));
    }
}
