<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Impl\MigrationsServiceDefinition;
use App\Application\Shared\ServiceDefinitionInterface;
use DI\ContainerBuilder;

final class MigrationsServiceDefinitionTest extends TestCase
{
    private MigrationsServiceDefinition $serviceDefinition;

    protected function setUp(): void
    {
        $this->serviceDefinition = new MigrationsServiceDefinition();
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

    public function testRegisterIncludesMigrationsConfig(): void
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

        $this->assertArrayHasKey('migrations.config', $capturedDefinitions);
    }

    public function testRegisterIncludesDependencyFactory(): void
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

        $this->assertArrayHasKey('Doctrine\Migrations\DependencyFactory', $capturedDefinitions);
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

        // Should have exactly 2 definitions
        $this->assertCount(2, $capturedDefinitions);
    }

    public function testCreateMigrationsConfigurationReturnsArray(): void
    {
        $result = MigrationsServiceDefinition::createMigrationsConfiguration();

        $this->assertIsArray($result);
    }

    public function testCreateMigrationsConfigurationStructure(): void
    {
        $result = MigrationsServiceDefinition::createMigrationsConfiguration();

        $this->assertArrayHasKey('table_storage', $result);
        $this->assertArrayHasKey('migrations_paths', $result);
        $this->assertArrayHasKey('all_or_nothing', $result);
        $this->assertArrayHasKey('check_database_platform', $result);
        $this->assertArrayHasKey('organize_migrations', $result);
    }

    public function testCreateMigrationsConfigurationTableStorage(): void
    {
        $result = MigrationsServiceDefinition::createMigrationsConfiguration();

        $tableStorage = $result['table_storage'];
        $this->assertIsArray($tableStorage);
        $this->assertArrayHasKey('table_name', $tableStorage);
        $this->assertArrayHasKey('version_column_name', $tableStorage);
        $this->assertArrayHasKey('version_column_length', $tableStorage);
        $this->assertArrayHasKey('executed_at_column_name', $tableStorage);
        
        $this->assertEquals('doctrine_migration_versions', $tableStorage['table_name']);
        $this->assertEquals('version', $tableStorage['version_column_name']);
        $this->assertEquals(191, $tableStorage['version_column_length']);
        $this->assertEquals('executed_at', $tableStorage['executed_at_column_name']);
    }

    public function testCreateMigrationsConfigurationMigrationsPaths(): void
    {
        $result = MigrationsServiceDefinition::createMigrationsConfiguration();

        $migrationsPaths = $result['migrations_paths'];
        $this->assertIsArray($migrationsPaths);
        $this->assertArrayHasKey('App\\Infrastructure\\Common\\Database\\Migrations', $migrationsPaths);
        
        $path = $migrationsPaths['App\\Infrastructure\\Common\\Database\\Migrations'];
        $this->assertIsString($path);
        $this->assertStringContainsString('/src/Infrastructure/Common/Database/Migrations', $path);
    }

    public function testCreateMigrationsConfigurationBooleanFlags(): void
    {
        $result = MigrationsServiceDefinition::createMigrationsConfiguration();

        $this->assertTrue($result['all_or_nothing']);
        $this->assertTrue($result['check_database_platform']);
        $this->assertEquals('year', $result['organize_migrations']);
    }

    public function testCreateMigrationsConfigurationConsistency(): void
    {
        $result1 = MigrationsServiceDefinition::createMigrationsConfiguration();
        $result2 = MigrationsServiceDefinition::createMigrationsConfiguration();

        // Should produce identical configurations
        $this->assertEquals($result1, $result2);
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

            $result = $this->serviceDefinition->register($builder);
            $this->assertNull($result); // register method returns void
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
        $definition = new MigrationsServiceDefinition();

        $this->assertInstanceOf(MigrationsServiceDefinition::class, $definition);
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

        // Should produce identical definition keys (same services)
        $this->assertEquals(array_keys($capturedDefinitions1), array_keys($capturedDefinitions2));
    }

    public function testStaticMethodsAreIndependent(): void
    {
        // Static methods should work without instance
        $config = MigrationsServiceDefinition::createMigrationsConfiguration();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);
    }
}
