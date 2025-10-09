<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Modules\System\Console\Commands\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Modules\System\Console\Commands\Impl\DoctrineTestCommand;
use App\Application\Modules\System\Console\Commands\CommandInterface;
use Symfony\Component\Console\Command\Command;

final class DoctrineTestCommandTest extends TestCase
{
    private DoctrineTestCommand $doctrineTestCommand;

    protected function setUp(): void
    {
        $this->doctrineTestCommand = new DoctrineTestCommand();
    }

    public function testImplementsExpectedInterface(): void
    {
        $this->assertInstanceOf(CommandInterface::class, $this->doctrineTestCommand);
    }

    public function testExtendsSymfonyCommand(): void
    {
        $this->assertInstanceOf(Command::class, $this->doctrineTestCommand);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $instance = new DoctrineTestCommand();

        $this->assertInstanceOf(DoctrineTestCommand::class, $instance);
        $this->assertInstanceOf(CommandInterface::class, $instance);
        $this->assertInstanceOf(Command::class, $instance);
    }

    public function testGetCommandReturnsSelf(): void
    {
        $result = $this->doctrineTestCommand->getCommand();

        $this->assertSame($this->doctrineTestCommand, $result);
        $this->assertInstanceOf(Command::class, $result);
    }

    public function testGetCommandIsIdempotent(): void
    {
        $result1 = $this->doctrineTestCommand->getCommand();
        $result2 = $this->doctrineTestCommand->getCommand();

        $this->assertSame($result1, $result2);
        $this->assertSame($this->doctrineTestCommand, $result1);
        $this->assertSame($this->doctrineTestCommand, $result2);
    }

    public function testCommandHasCorrectName(): void
    {
        $command = $this->doctrineTestCommand->getCommand();

        $this->assertEquals('system:doctrine:test', $command->getName());
    }

    public function testCommandHasDescription(): void
    {
        $command = $this->doctrineTestCommand->getCommand();

        $this->assertEquals('Testar conexões e funcionalidades do Doctrine ORM', $command->getDescription());
    }

    public function testImplementsCommandInterfaceContract(): void
    {
        $this->assertTrue(method_exists($this->doctrineTestCommand, 'getCommand'));
        
        $returnType = (new \ReflectionMethod($this->doctrineTestCommand, 'getCommand'))->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Symfony\Component\Console\Command\Command', $returnType->getName());
    }

    public function testConstructorWithoutArguments(): void
    {
        $instance = new DoctrineTestCommand();

        $this->assertInstanceOf(DoctrineTestCommand::class, $instance);
        $this->assertInstanceOf(Command::class, $instance);
    }

    public function testIsCommandFinalClass(): void
    {
        $reflection = new \ReflectionClass(DoctrineTestCommand::class);
        
        $this->assertTrue($reflection->isFinal());
    }

    public function testCommandConstruction(): void
    {
        $instance = new DoctrineTestCommand();
        $command = $instance->getCommand();

        $this->assertInstanceOf(Command::class, $command);
        $this->assertEquals('system:doctrine:test', $command->getName());
        $this->assertEquals('Testar conexões e funcionalidades do Doctrine ORM', $command->getDescription());
    }

    public function testMultipleGetCommandCallsReturnSameInstance(): void
    {
        $calls = [];
        for ($i = 0; $i < 5; $i++) {
            $calls[] = $this->doctrineTestCommand->getCommand();
        }

        foreach ($calls as $call) {
            $this->assertSame($this->doctrineTestCommand, $call);
        }
    }
}
