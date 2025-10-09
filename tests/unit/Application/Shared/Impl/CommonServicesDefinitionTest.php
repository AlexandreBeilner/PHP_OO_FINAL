<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Impl\CommonServicesDefinition;
use App\Application\Shared\ServiceDefinitionInterface;
use DI\ContainerBuilder;

final class CommonServicesDefinitionTest extends TestCase
{
    private CommonServicesDefinition $serviceDefinition;

    protected function setUp(): void
    {
        $this->serviceDefinition = new CommonServicesDefinition();
    }

    public function testImplementsServiceDefinitionInterface(): void
    {
        $this->assertInstanceOf(ServiceDefinitionInterface::class, $this->serviceDefinition);
    }

    public function testRegisterCallsAddDefinitionsOnBuilder(): void
    {
        $builder = $this->createMock(ContainerBuilder::class);

        $builder->expects($this->once())
            ->method('addDefinitions')
            ->with($this->isType('array'))
            ->willReturnSelf();

        $this->serviceDefinition->register($builder);
    }

    public function testRegisterAddsCorrectServiceDefinitions(): void
    {
        $builder = $this->createMock(ContainerBuilder::class);
        $capturedDefinitions = null;

        $builder->expects($this->once())
            ->method('addDefinitions')
            ->willReturnCallback(function($definitions) use (&$capturedDefinitions, $builder) {
                $capturedDefinitions = $definitions;
                return $builder;
            });

        $this->serviceDefinition->register($builder);

        $this->assertIsArray($capturedDefinitions);
        $this->assertNotEmpty($capturedDefinitions);
    }

    public function testRegisterIncludesEmailValidatorDefinition(): void
    {
        $builder = $this->createMock(ContainerBuilder::class);
        $capturedDefinitions = null;

        $builder->expects($this->once())
            ->method('addDefinitions')
            ->willReturnCallback(function($definitions) use (&$capturedDefinitions, $builder) {
                $capturedDefinitions = $definitions;
                return $builder;
            });

        $this->serviceDefinition->register($builder);

        $this->assertArrayHasKey('App\Domain\Common\Validators\Impl\EmailValidator', $capturedDefinitions);
    }

    public function testRegisterIncludesBehaviorDefinitions(): void
    {
        $builder = $this->createMock(ContainerBuilder::class);
        $capturedDefinitions = null;

        $builder->expects($this->once())
            ->method('addDefinitions')
            ->willReturnCallback(function($definitions) use (&$capturedDefinitions, $builder) {
                $capturedDefinitions = $definitions;
                return $builder;
            });

        $this->serviceDefinition->register($builder);

        $expectedBehaviors = [
            'App\Domain\Common\Entities\Behaviors\Impl\TimestampableBehavior',
            'App\Domain\Common\Entities\Behaviors\Impl\SoftDeletableBehavior',
            'App\Domain\Common\Entities\Behaviors\Impl\UuidableBehavior'
        ];

        foreach ($expectedBehaviors as $behaviorClass) {
            $this->assertArrayHasKey($behaviorClass, $capturedDefinitions, "Definition should include {$behaviorClass}");
        }
    }

    public function testRegisterAddsExactNumberOfDefinitions(): void
    {
        $builder = $this->createMock(ContainerBuilder::class);
        $capturedDefinitions = null;

        $builder->expects($this->once())
            ->method('addDefinitions')
            ->willReturnCallback(function($definitions) use (&$capturedDefinitions, $builder) {
                $capturedDefinitions = $definitions;
                return $builder;
            });

        $this->serviceDefinition->register($builder);

        // Should have exactly 4 definitions: EmailValidator + 3 Behaviors
        $this->assertCount(4, $capturedDefinitions);
    }

    public function testRegisterCanBeCalledMultipleTimes(): void
    {
        $builder1 = $this->createMock(ContainerBuilder::class);
        $builder2 = $this->createMock(ContainerBuilder::class);

        $builder1->expects($this->once())->method('addDefinitions');
        $builder2->expects($this->once())->method('addDefinitions');

        // Should not throw exceptions when called multiple times
        $this->serviceDefinition->register($builder1);
        $this->serviceDefinition->register($builder2);

        // If we reach this point, the test passes

    }

    public function testRegisterWithDifferentBuilders(): void
    {
        $builders = [
            $this->createMock(ContainerBuilder::class),
            $this->createMock(ContainerBuilder::class),
            $this->createMock(ContainerBuilder::class)
        ];

        foreach ($builders as $builder) {
            $builder->expects($this->once())
                ->method('addDefinitions')
                ->with($this->isType('array'))
                ->willReturnSelf();

            $this->serviceDefinition->register($builder);
        }

        // Test passes if no exceptions thrown - mock expectations verify the behavior
    }

    public function testRegisterDefinitionsContainAutowireCallables(): void
    {
        $builder = $this->createMock(ContainerBuilder::class);
        $capturedDefinitions = null;

        $builder->expects($this->once())
            ->method('addDefinitions')
            ->willReturnCallback(function($definitions) use (&$capturedDefinitions, $builder) {
                $capturedDefinitions = $definitions;
                return $builder;
            });

        $this->serviceDefinition->register($builder);

        // Each definition should be some kind of callable/definition object
        foreach ($capturedDefinitions as $className => $definition) {
            $this->assertIsString($className, "Class name should be string");
            $this->assertNotNull($definition, "Definition should not be null");
        }
    }

    public function testRegisterVoidReturnType(): void
    {
        $builder = $this->createMock(ContainerBuilder::class);
        $builder->method('addDefinitions')->willReturnSelf();

        $result = $this->serviceDefinition->register($builder);

        $this->assertNull($result);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $definition = new CommonServicesDefinition();

        $this->assertInstanceOf(CommonServicesDefinition::class, $definition);
        $this->assertInstanceOf(ServiceDefinitionInterface::class, $definition);
    }

    public function testServiceDefinitionIsStateless(): void
    {
        $builder1 = $this->createMock(ContainerBuilder::class);
        $builder2 = $this->createMock(ContainerBuilder::class);

        $capturedDefinitions1 = null;
        $capturedDefinitions2 = null;

        $builder1->expects($this->once())
            ->method('addDefinitions')
            ->willReturnCallback(function($definitions) use (&$capturedDefinitions1, $builder1) {
                $capturedDefinitions1 = $definitions;
                return $builder1;
            });

        $builder2->expects($this->once())
            ->method('addDefinitions')
            ->willReturnCallback(function($definitions) use (&$capturedDefinitions2, $builder2) {
                $capturedDefinitions2 = $definitions;
                return $builder2;
            });

        // Call register twice with different builders
        $this->serviceDefinition->register($builder1);
        $this->serviceDefinition->register($builder2);

        // Should produce identical definitions
        $this->assertEquals($capturedDefinitions1, $capturedDefinitions2);
    }
}
