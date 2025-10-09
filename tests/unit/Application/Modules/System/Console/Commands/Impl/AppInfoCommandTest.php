<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Modules\System\Console\Commands\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Modules\System\Console\Commands\Impl\AppInfoCommand;
use App\Application\Modules\System\Console\Commands\CommandInterface;
use Symfony\Component\Console\Command\Command;

final class AppInfoCommandTest extends TestCase
{
    private AppInfoCommand $appInfoCommand;

    protected function setUp(): void
    {
        $this->appInfoCommand = new AppInfoCommand();
    }

    public function testImplementsExpectedInterface(): void
    {
        $this->assertInstanceOf(CommandInterface::class, $this->appInfoCommand);
    }

    public function testExtendsSymfonyCommand(): void
    {
        $this->assertInstanceOf(Command::class, $this->appInfoCommand);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $instance = new AppInfoCommand();

        $this->assertInstanceOf(AppInfoCommand::class, $instance);
        $this->assertInstanceOf(CommandInterface::class, $instance);
        $this->assertInstanceOf(Command::class, $instance);
    }

    public function testGetCommandReturnsSelf(): void
    {
        $result = $this->appInfoCommand->getCommand();

        $this->assertSame($this->appInfoCommand, $result);
        $this->assertInstanceOf(Command::class, $result);
    }

    public function testGetCommandIsIdempotent(): void
    {
        $result1 = $this->appInfoCommand->getCommand();
        $result2 = $this->appInfoCommand->getCommand();

        $this->assertSame($result1, $result2);
        $this->assertSame($this->appInfoCommand, $result1);
        $this->assertSame($this->appInfoCommand, $result2);
    }

    public function testCommandHasCorrectName(): void
    {
        $command = $this->appInfoCommand->getCommand();

        $this->assertEquals('system:app:info', $command->getName());
    }

    public function testCommandHasDescription(): void
    {
        $command = $this->appInfoCommand->getCommand();

        $this->assertEquals('Mostrar informações da aplicação', $command->getDescription());
    }

    public function testImplementsCommandInterfaceContract(): void
    {
        $this->assertTrue(method_exists($this->appInfoCommand, 'getCommand'));
        
        $command = $this->appInfoCommand->getCommand();
        $this->assertInstanceOf('Symfony\Component\Console\Command\Command', $command);
        $this->assertSame($this->appInfoCommand, $command);
    }

    public function testConstructorWithoutArguments(): void
    {
        $instance = new AppInfoCommand();

        $this->assertInstanceOf(AppInfoCommand::class, $instance);
        $this->assertInstanceOf(Command::class, $instance);
    }

    public function testIsCommandFinalClass(): void
    {
        $reflection = new \ReflectionClass(AppInfoCommand::class);
        
        $this->assertTrue($reflection->isFinal());
    }

    public function testCommandConstruction(): void
    {
        $instance = new AppInfoCommand();
        $command = $instance->getCommand();

        $this->assertInstanceOf(Command::class, $command);
        $this->assertEquals('system:app:info', $command->getName());
        $this->assertEquals('Mostrar informações da aplicação', $command->getDescription());
    }

    public function testMultipleGetCommandCallsReturnSameInstance(): void
    {
        $calls = [];
        for ($i = 0; $i < 5; $i++) {
            $calls[] = $this->appInfoCommand->getCommand();
        }

        foreach ($calls as $call) {
            $this->assertSame($this->appInfoCommand, $call);
        }
    }

    public function testCommandNamespace(): void
    {
        $command = $this->appInfoCommand->getCommand();
        $name = $command->getName();
        
        $this->assertStringStartsWith('system:', $name);
        $this->assertStringContainsString('app', $name);
        $this->assertStringContainsString('info', $name);
    }

    public function testCommandPropertiesViaPublicInterface(): void
    {
        // Test command properties through public API, not private properties
        $this->assertEquals('system:app:info', $this->appInfoCommand->getName());
        $this->assertEquals('Mostrar informações da aplicação', $this->appInfoCommand->getDescription());
        
        // Ensure consistency across multiple calls
        $this->assertEquals($this->appInfoCommand->getName(), $this->appInfoCommand->getCommand()->getName());
        $this->assertEquals($this->appInfoCommand->getDescription(), $this->appInfoCommand->getCommand()->getDescription());
    }

    public function testGetCommandConsistency(): void
    {
        $command1 = $this->appInfoCommand->getCommand();
        $command2 = $this->appInfoCommand->getCommand();
        
        $this->assertSame($command1, $command2);
        $this->assertEquals($command1->getName(), $command2->getName());
        $this->assertEquals($command1->getDescription(), $command2->getDescription());
    }
}
