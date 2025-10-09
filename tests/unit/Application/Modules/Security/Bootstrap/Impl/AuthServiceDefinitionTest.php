<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Modules\Security\Bootstrap\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Modules\Security\Bootstrap\Impl\AuthServiceDefinition;
use App\Application\Shared\ServiceDefinitionInterface;
use DI\ContainerBuilder;

final class AuthServiceDefinitionTest extends TestCase
{
    private AuthServiceDefinition $serviceDefinition;

    protected function setUp(): void
    {
        $this->serviceDefinition = new AuthServiceDefinition();
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

    public function testRegisterAddsCorrectServiceDefinition(): void
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

    public function testRegisterIncludesAuthServiceInterface(): void
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

        $this->assertArrayHasKey('App\Domain\Security\Services\AuthServiceInterface', $capturedDefinitions);
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

        // Should have exactly 1 definition
        $this->assertCount(1, $capturedDefinitions);
    }

    public function testRegisterCanBeCalledMultipleTimes(): void
    {
        $builder1 = $this->createMock(ContainerBuilder::class);
        $builder2 = $this->createMock(ContainerBuilder::class);

        $builder1->expects($this->once())->method('addDefinitions')->willReturnSelf();
        $builder2->expects($this->once())->method('addDefinitions')->willReturnSelf();

        // Should not throw exceptions when called multiple times
        $this->serviceDefinition->register($builder1);
        $this->serviceDefinition->register($builder2);
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

    public function testRegisterDefinitionContainsCallable(): void
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
        foreach ($capturedDefinitions as $interfaceName => $definition) {
            $this->assertIsString($interfaceName, "Interface name should be string");
            $this->assertIsCallable($definition, "Definition should be callable");
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
        $definition = new AuthServiceDefinition();

        $this->assertInstanceOf(AuthServiceDefinition::class, $definition);
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

        // Should produce identical definition keys (same interfaces)
        $this->assertEquals(array_keys($capturedDefinitions1), array_keys($capturedDefinitions2));
    }
}
