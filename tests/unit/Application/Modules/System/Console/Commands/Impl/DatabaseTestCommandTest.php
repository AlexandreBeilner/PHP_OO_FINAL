<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Modules\System\Console\Commands\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Modules\System\Console\Commands\Impl\DatabaseTestCommand;
use App\Application\Modules\System\Console\Commands\CommandInterface;
use Symfony\Component\Console\Command\Command;

final class DatabaseTestCommandTest extends TestCase
{
    private DatabaseTestCommand $databaseTestCommand;

    protected function setUp(): void
    {
        $this->databaseTestCommand = new DatabaseTestCommand();
    }

    public function testImplementsExpectedInterface(): void
    {
        $this->assertInstanceOf(CommandInterface::class, $this->databaseTestCommand);
    }

    public function testExtendsSymfonyCommand(): void
    {
        $this->assertInstanceOf(Command::class, $this->databaseTestCommand);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $instance = new DatabaseTestCommand();

        $this->assertInstanceOf(DatabaseTestCommand::class, $instance);
        $this->assertInstanceOf(CommandInterface::class, $instance);
        $this->assertInstanceOf(Command::class, $instance);
    }

    public function testGetCommandReturnsSelf(): void
    {
        $result = $this->databaseTestCommand->getCommand();

        $this->assertSame($this->databaseTestCommand, $result);
        $this->assertInstanceOf(Command::class, $result);
    }

    public function testGetCommandIsIdempotent(): void
    {
        $result1 = $this->databaseTestCommand->getCommand();
        $result2 = $this->databaseTestCommand->getCommand();

        $this->assertSame($result1, $result2);
        $this->assertSame($this->databaseTestCommand, $result1);
        $this->assertSame($this->databaseTestCommand, $result2);
    }

    public function testCommandHasCorrectName(): void
    {
        $command = $this->databaseTestCommand->getCommand();

        $this->assertEquals('system:database:test', $command->getName());
    }

    public function testCommandHasDescription(): void
    {
        $command = $this->databaseTestCommand->getCommand();

        $this->assertEquals('Testar conexões de banco de dados', $command->getDescription());
    }

    public function testImplementsCommandInterfaceContract(): void
    {
        $this->assertTrue(method_exists($this->databaseTestCommand, 'getCommand'));
        
        $command = $this->databaseTestCommand->getCommand();
        $this->assertInstanceOf('Symfony\Component\Console\Command\Command', $command);
        $this->assertSame($this->databaseTestCommand, $command);
    }

    public function testConstructorWithoutArguments(): void
    {
        $instance = new DatabaseTestCommand();

        $this->assertInstanceOf(DatabaseTestCommand::class, $instance);
        $this->assertInstanceOf(Command::class, $instance);
    }

    public function testIsCommandFinalClass(): void
    {
        $reflection = new \ReflectionClass(DatabaseTestCommand::class);
        
        $this->assertTrue($reflection->isFinal());
    }

    public function testCommandConstruction(): void
    {
        $instance = new DatabaseTestCommand();
        $command = $instance->getCommand();

        $this->assertInstanceOf(Command::class, $command);
        $this->assertEquals('system:database:test', $command->getName());
        $this->assertEquals('Testar conexões de banco de dados', $command->getDescription());
    }

    public function testMultipleGetCommandCallsReturnSameInstance(): void
    {
        $calls = [];
        for ($i = 0; $i < 5; $i++) {
            $calls[] = $this->databaseTestCommand->getCommand();
        }

        foreach ($calls as $call) {
            $this->assertSame($this->databaseTestCommand, $call);
        }
    }

    public function testCommandNamespace(): void
    {
        $command = $this->databaseTestCommand->getCommand();
        $name = $command->getName();
        
        $this->assertStringStartsWith('system:', $name);
        $this->assertStringContainsString('database', $name);
        $this->assertStringContainsString('test', $name);
    }

    public function testCommandPropertiesViaPublicInterface(): void
    {
        // Test command properties through public API, not private properties
        $this->assertEquals('system:database:test', $this->databaseTestCommand->getName());
        $this->assertEquals('Testar conexões de banco de dados', $this->databaseTestCommand->getDescription());
        
        // Ensure consistency across multiple calls
        $this->assertEquals($this->databaseTestCommand->getName(), $this->databaseTestCommand->getCommand()->getName());
        $this->assertEquals($this->databaseTestCommand->getDescription(), $this->databaseTestCommand->getCommand()->getDescription());
    }

    public function testGetCommandConsistency(): void
    {
        $command1 = $this->databaseTestCommand->getCommand();
        $command2 = $this->databaseTestCommand->getCommand();
        
        $this->assertSame($command1, $command2);
        $this->assertEquals($command1->getName(), $command2->getName());
        $this->assertEquals($command1->getDescription(), $command2->getDescription());
    }

    public function testConstructorCallsParentConstructor(): void
    {
        $instance = new DatabaseTestCommand();
        
        // Verify that the command has been properly initialized
        $this->assertNotNull($instance->getName());
        $this->assertNotNull($instance->getDescription());
    }

    public function testCommandIsConfiguredAfterConstruction(): void
    {
        $instance = new DatabaseTestCommand();
        
        $this->assertEquals('system:database:test', $instance->getName());
        $this->assertEquals('Testar conexões de banco de dados', $instance->getDescription());
    }

    public function testMultipleConstructorCalls(): void
    {
        $instance1 = new DatabaseTestCommand();
        $instance2 = new DatabaseTestCommand();
        
        $this->assertNotSame($instance1, $instance2);
        $this->assertEquals($instance1->getName(), $instance2->getName());
        $this->assertEquals($instance1->getDescription(), $instance2->getDescription());
    }
}