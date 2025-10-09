<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Modules\Security\Bootstrap\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Modules\Security\Bootstrap\Impl\UserServiceDefinition;
use App\Application\Shared\ServiceDefinitionInterface;
use DI\ContainerBuilder;

final class UserServiceDefinitionTest extends TestCase
{
    private UserServiceDefinition $serviceDefinition;

    protected function setUp(): void
    {
        $this->serviceDefinition = new UserServiceDefinition();
    }

    public function testImplementsServiceDefinitionInterface(): void
    {
        $this->assertInstanceOf(ServiceDefinitionInterface::class, $this->serviceDefinition);
    }

    public function testRegisterCallsAddDefinitionsOnBuilder(): void
    {
        $builder = $this->createMock(ContainerBuilder::class);

        $builder->expects($this->exactly(2))
            ->method('addDefinitions')
            ->with($this->isType('array'))
            ->willReturnSelf();

        $this->serviceDefinition->register($builder);
    }

    public function testRegisterAddsUserRepositoryAndServiceDefinitions(): void
    {
        $builder = $this->createMock(ContainerBuilder::class);
        $capturedDefinitions = [];

        $builder->expects($this->exactly(2))
            ->method('addDefinitions')
            ->willReturnCallback(function($definitions) use (&$capturedDefinitions, $builder) {
                $capturedDefinitions[] = $definitions;
                return $builder;
            });

        $this->serviceDefinition->register($builder);

        $this->assertCount(2, $capturedDefinitions);
        $this->assertIsArray($capturedDefinitions[0]);
        $this->assertIsArray($capturedDefinitions[1]);
    }

    public function testRegisterIncludesUserRepositoryInterface(): void
    {
        $builder = $this->createMock(ContainerBuilder::class);
        $capturedDefinitions = [];

        $builder->expects($this->exactly(2))
            ->method('addDefinitions')
            ->willReturnCallback(function($definitions) use (&$capturedDefinitions, $builder) {
                $capturedDefinitions[] = $definitions;
                return $builder;
            });

        $this->serviceDefinition->register($builder);

        // First call should register UserRepositoryInterface
        $firstDefinitions = $capturedDefinitions[0];
        $this->assertArrayHasKey('App\Domain\Security\Repositories\UserRepositoryInterface', $firstDefinitions);
    }

    public function testRegisterIncludesUserServiceInterface(): void
    {
        $builder = $this->createMock(ContainerBuilder::class);
        $capturedDefinitions = [];

        $builder->expects($this->exactly(2))
            ->method('addDefinitions')
            ->willReturnCallback(function($definitions) use (&$capturedDefinitions, $builder) {
                $capturedDefinitions[] = $definitions;
                return $builder;
            });

        $this->serviceDefinition->register($builder);

        // Second call should register UserServiceInterface
        $secondDefinitions = $capturedDefinitions[1];
        $this->assertArrayHasKey('App\Domain\Security\Services\UserServiceInterface', $secondDefinitions);
    }

    public function testRegisterAddsExactNumberOfDefinitions(): void
    {
        $builder = $this->createMock(ContainerBuilder::class);
        $capturedDefinitions = [];

        $builder->expects($this->exactly(2))
            ->method('addDefinitions')
            ->willReturnCallback(function($definitions) use (&$capturedDefinitions, $builder) {
                $capturedDefinitions[] = $definitions;
                return $builder;
            });

        $this->serviceDefinition->register($builder);

        // First call should have 1 definition (UserRepositoryInterface)
        $this->assertCount(1, $capturedDefinitions[0]);
        
        // Second call should have 1 definition (UserServiceInterface)
        $this->assertCount(1, $capturedDefinitions[1]);
    }

    public function testRegisterCanBeCalledMultipleTimes(): void
    {
        $builder1 = $this->createMock(ContainerBuilder::class);
        $builder2 = $this->createMock(ContainerBuilder::class);

        $builder1->expects($this->exactly(2))->method('addDefinitions')->willReturnSelf();
        $builder2->expects($this->exactly(2))->method('addDefinitions')->willReturnSelf();

        // Should not throw exceptions when called multiple times
        $this->serviceDefinition->register($builder1);
        $this->serviceDefinition->register($builder2);
    }

    public function testRegisterDefinitionsContainCallables(): void
    {
        $builder = $this->createMock(ContainerBuilder::class);
        $capturedDefinitions = [];

        $builder->expects($this->exactly(2))
            ->method('addDefinitions')
            ->willReturnCallback(function($definitions) use (&$capturedDefinitions, $builder) {
                $capturedDefinitions[] = $definitions;
                return $builder;
            });

        $this->serviceDefinition->register($builder);

        // Verify each definition contains callable values
        foreach ($capturedDefinitions as $definitionGroup) {
            foreach ($definitionGroup as $interface => $definition) {
                $this->assertIsString($interface, "Interface name should be string");
                $this->assertIsCallable($definition, "Definition should be callable");
            }
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
        $definition = new UserServiceDefinition();

        $this->assertInstanceOf(UserServiceDefinition::class, $definition);
        $this->assertInstanceOf(ServiceDefinitionInterface::class, $definition);
    }

    public function testServiceDefinitionIsStateless(): void
    {
        $builder1 = $this->createMock(ContainerBuilder::class);
        $builder2 = $this->createMock(ContainerBuilder::class);

        $capturedDefinitions1 = [];
        $capturedDefinitions2 = [];

        $builder1->expects($this->exactly(2))
            ->method('addDefinitions')
            ->willReturnCallback(function($definitions) use (&$capturedDefinitions1, $builder1) {
                $capturedDefinitions1[] = $definitions;
                return $builder1;
            });

        $builder2->expects($this->exactly(2))
            ->method('addDefinitions')
            ->willReturnCallback(function($definitions) use (&$capturedDefinitions2, $builder2) {
                $capturedDefinitions2[] = $definitions;
                return $builder2;
            });

        // Call register twice with different builders
        $this->serviceDefinition->register($builder1);
        $this->serviceDefinition->register($builder2);

        // Should produce similar definition structures (same keys)
        $this->assertCount(2, $capturedDefinitions1);
        $this->assertCount(2, $capturedDefinitions2);
        
        // Check that the same interfaces are registered
        $keys1 = array_keys($capturedDefinitions1[0]);
        $keys2 = array_keys($capturedDefinitions2[0]);
        $this->assertEquals($keys1, $keys2);

        $keys1 = array_keys($capturedDefinitions1[1]);
        $keys2 = array_keys($capturedDefinitions2[1]);
        $this->assertEquals($keys1, $keys2);
    }
}
