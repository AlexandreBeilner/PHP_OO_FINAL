<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Modules\System\Console\Commands\Impl;

use App\Application\Modules\System\Console\Commands\CommandInterface;
use App\Application\Modules\System\Console\Commands\Impl\CacheClearCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

final class CacheClearCommandTest extends TestCase
{
    private CacheClearCommand $command;

    protected function setUp(): void
    {
        $this->command = new CacheClearCommand();
    }

    public function testImplementsCommandInterface(): void
    {
        $this->assertInstanceOf(CommandInterface::class, $this->command);
    }

    public function testExtendsSymfonyCommand(): void
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $instance = new CacheClearCommand();

        $this->assertInstanceOf(CacheClearCommand::class, $instance);
    }

    public function testGetCommandReturnsItself(): void
    {
        $result = $this->command->getCommand();

        $this->assertSame($this->command, $result);
    }

    public function testHasCorrectDefaultName(): void
    {
        $this->assertEquals('system:cache:clear', $this->command->getName());
    }

    public function testHasCorrectDefaultDescription(): void
    {
        $this->assertEquals('Clear application cache', $this->command->getDescription());
    }

    public function testHasAllOption(): void
    {
        $definition = $this->command->getDefinition();
        
        $this->assertTrue($definition->hasOption('all'));
        $this->assertEquals('Clear all cache types', $definition->getOption('all')->getDescription());
    }

    public function testHasDiOption(): void
    {
        $definition = $this->command->getDefinition();
        
        $this->assertTrue($definition->hasOption('di'));
        $this->assertEquals('Clear DI container cache only', $definition->getOption('di')->getDescription());
    }

    public function testHasCompiledOption(): void
    {
        $definition = $this->command->getDefinition();
        
        $this->assertTrue($definition->hasOption('compiled'));
        $this->assertEquals('Clear compiled container only', $definition->getOption('compiled')->getDescription());
    }

    public function testCommandIsEnabled(): void
    {
        $this->assertTrue($this->command->isEnabled());
    }

    public function testCommandHasCorrectAliases(): void
    {
        $aliases = $this->command->getAliases();
        
        $this->assertIsArray($aliases);
    }

    public function testCommandDefinitionIsComplete(): void
    {
        $definition = $this->command->getDefinition();
        
        $this->assertNotNull($definition);
        $this->assertTrue($definition->hasOption('all'));
        $this->assertTrue($definition->hasOption('di'));
        $this->assertTrue($definition->hasOption('compiled'));
    }

    public function testCommandOptionsHaveCorrectDefaults(): void
    {
        $definition = $this->command->getDefinition();
        
        $allOption = $definition->getOption('all');
        $diOption = $definition->getOption('di');
        $compiledOption = $definition->getOption('compiled');
        
        $this->assertFalse($allOption->isValueRequired());
        $this->assertFalse($diOption->isValueRequired());
        $this->assertFalse($compiledOption->isValueRequired());
    }

    public function testCommandOptionsAreOptional(): void
    {
        $definition = $this->command->getDefinition();
        
        $allOption = $definition->getOption('all');
        $diOption = $definition->getOption('di');
        $compiledOption = $definition->getOption('compiled');
        
        $this->assertFalse($allOption->acceptValue());
        $this->assertFalse($diOption->acceptValue());
        $this->assertFalse($compiledOption->acceptValue());
    }

    public function testCommandHasValidConfiguration(): void
    {
        $this->assertNotEmpty($this->command->getName());
        $this->assertNotEmpty($this->command->getDescription());
        $this->assertInstanceOf(CommandInterface::class, $this->command);
        $this->assertInstanceOf(Command::class, $this->command);
    }
}