<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Impl\BootstrapOrchestratorServiceDefinition;
use App\Application\Shared\ServiceDefinitionInterface;
use DI\ContainerBuilder;

final class BootstrapOrchestratorServiceDefinitionTest extends TestCase
{
    private BootstrapOrchestratorServiceDefinition $serviceDefinition;

    protected function setUp(): void
    {
        $this->serviceDefinition = new BootstrapOrchestratorServiceDefinition();
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

    public function testRegisterIncludesBootstrapRegistryInterface(): void
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

        $this->assertArrayHasKey('App\Application\Shared\Registry\BootstrapRegistryInterface', $capturedDefinitions);
    }

    public function testRegisterIncludesBootstrapLoaderInterface(): void
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

        $this->assertArrayHasKey('App\Application\Shared\Loader\BootstrapLoaderInterface', $capturedDefinitions);
    }

    public function testRegisterIncludesRouteLoaderInterface(): void
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

        $this->assertArrayHasKey('App\Application\Shared\Loader\RouteLoaderInterface', $capturedDefinitions);
    }

    public function testRegisterIncludesLoaderBundle(): void
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

        $this->assertArrayHasKey('App\Application\Shared\Orchestrator\LoaderBundle', $capturedDefinitions);
    }

    public function testRegisterIncludesBootstrapOrchestratorInterface(): void
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

        $this->assertArrayHasKey('App\Application\Shared\Orchestrator\BootstrapOrchestratorInterface', $capturedDefinitions);
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

        // Should have exactly 5 definitions
        $this->assertCount(5, $capturedDefinitions);
    }

    public function testRegisterCanBeCalledMultipleTimes(): void
    {
        $builder1 = $this->createMock(ContainerBuilder::class);
        $builder2 = $this->createMock(ContainerBuilder::class);

        $builder1->expects($this->once())->method('addDefinitions')->willReturnSelf();
        $builder2->expects($this->once())->method('addDefinitions')->willReturnSelf();

        // Should work with different builders and return void
        $result1 = $this->serviceDefinition->register($builder1);
        $result2 = $this->serviceDefinition->register($builder2);
        
        $this->assertNull($result1);
        $this->assertNull($result2);
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

            $result = $this->serviceDefinition->register($builder);
            $this->assertNull($result); // register method returns void
        }
    }

    public function testRegisterDefinitionContainsCallables(): void
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

        // Each definition should be a callable (closure/factory)
        foreach ($capturedDefinitions as $interfaceName => $definition) {
            $this->assertIsString($interfaceName, "Interface name should be string");
            $this->assertTrue(is_callable($definition), "Definition should be callable for {$interfaceName}");
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
        $definition = new BootstrapOrchestratorServiceDefinition();

        $this->assertInstanceOf(BootstrapOrchestratorServiceDefinition::class, $definition);
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

    public function testRegisterIncludesAllRequiredInterfaces(): void
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

        $expectedInterfaces = [
            'App\Application\Shared\Registry\BootstrapRegistryInterface',
            'App\Application\Shared\Loader\BootstrapLoaderInterface',
            'App\Application\Shared\Loader\RouteLoaderInterface',
            'App\Application\Shared\Orchestrator\LoaderBundle',
            'App\Application\Shared\Orchestrator\BootstrapOrchestratorInterface'
        ];

        foreach ($expectedInterfaces as $interface) {
            $this->assertArrayHasKey($interface, $capturedDefinitions, "Missing interface: {$interface}");
        }
    }

    public function testRegisterStructureIsComplete(): void
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

        // Ensure we have exactly the expected keys (no extra keys)
        $expectedInterfaces = [
            'App\Application\Shared\Registry\BootstrapRegistryInterface',
            'App\Application\Shared\Loader\BootstrapLoaderInterface',
            'App\Application\Shared\Loader\RouteLoaderInterface',
            'App\Application\Shared\Orchestrator\LoaderBundle',
            'App\Application\Shared\Orchestrator\BootstrapOrchestratorInterface'
        ];

        $this->assertCount(count($expectedInterfaces), $capturedDefinitions);
        $this->assertEquals($expectedInterfaces, array_keys($capturedDefinitions));
    }

    public function testMultipleInstancesBehaveSimilarly(): void
    {
        $definition1 = new BootstrapOrchestratorServiceDefinition();
        $definition2 = new BootstrapOrchestratorServiceDefinition();

        $builder1 = $this->createMock(ContainerBuilder::class);
        $builder2 = $this->createMock(ContainerBuilder::class);

        $capturedDefinitions1 = null;
        $capturedDefinitions2 = null;

        $builder1->method('addDefinitions')->willReturnCallback(function($definitions) use (&$capturedDefinitions1, $builder1) {
            $capturedDefinitions1 = $definitions;
            return $builder1;
        });

        $builder2->method('addDefinitions')->willReturnCallback(function($definitions) use (&$capturedDefinitions2, $builder2) {
            $capturedDefinitions2 = $definitions;
            return $builder2;
        });

        $definition1->register($builder1);
        $definition2->register($builder2);

        // Different instances should register the same interfaces
        $this->assertEquals(array_keys($capturedDefinitions1), array_keys($capturedDefinitions2));
    }
}
