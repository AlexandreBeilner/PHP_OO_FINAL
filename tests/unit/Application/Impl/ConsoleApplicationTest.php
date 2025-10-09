<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Impl\ConsoleApplication;
use App\Application\ConsoleApplicationInterface;
use App\Application\ApplicationInterface;
use Symfony\Component\Console\Application as SymfonyApplication;

final class ConsoleApplicationTest extends TestCase
{
    public function testImplementsConsoleApplicationInterface(): void
    {
        $consoleApp = new ConsoleApplication();

        $this->assertInstanceOf(ConsoleApplicationInterface::class, $consoleApp);
    }

    public function testExtendsSymfonyApplication(): void
    {
        $consoleApp = new ConsoleApplication();

        $this->assertInstanceOf(SymfonyApplication::class, $consoleApp);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $consoleApp = new ConsoleApplication();

        $this->assertInstanceOf(ConsoleApplication::class, $consoleApp);
        $this->assertInstanceOf(ConsoleApplicationInterface::class, $consoleApp);
        $this->assertInstanceOf(SymfonyApplication::class, $consoleApp);
    }

    public function testConstructorSetsApplicationNameAndVersion(): void
    {
        $consoleApp = new ConsoleApplication();

        $this->assertEquals('PHP-OO Console', $consoleApp->getName());
        $this->assertEquals('1.0.0', $consoleApp->getVersion());
    }

    public function testGetAppReturnsApplicationInterface(): void
    {
        $consoleApp = new ConsoleApplication();

        $app = $consoleApp->getApp();

        $this->assertInstanceOf(ApplicationInterface::class, $app);
    }

    public function testGetAppReturnsSameInstanceOnMultipleCalls(): void
    {
        $consoleApp = new ConsoleApplication();

        $app1 = $consoleApp->getApp();
        $app2 = $consoleApp->getApp();

        $this->assertSame($app1, $app2);
        $this->assertInstanceOf(ApplicationInterface::class, $app1);
        $this->assertInstanceOf(ApplicationInterface::class, $app2);
    }

    public function testConsoleApplicationHasCommands(): void
    {
        $consoleApp = new ConsoleApplication();

        $commands = $consoleApp->all();

        $this->assertIsArray($commands);
        $this->assertNotEmpty($commands);
    }

    public function testConsoleApplicationHasSystemCommands(): void
    {
        $consoleApp = new ConsoleApplication();
        $allCommands = array_keys($consoleApp->all());

        // Should have at least some system commands registered
        $systemCommandsFound = 0;
        $possibleSystemCommands = [
            'system:database:test',
            'system:doctrine:test', 
            'system:cache:clear',
            'app:info'
        ];

        foreach ($possibleSystemCommands as $command) {
            if ($consoleApp->has($command)) {
                $systemCommandsFound++;
            }
        }

        // Should have at least 2 system commands registered
        $this->assertGreaterThanOrEqual(2, $systemCommandsFound);
    }

    public function testConsoleApplicationCanExecuteCommands(): void
    {
        $consoleApp = new ConsoleApplication();
        $allCommands = array_keys($consoleApp->all());

        // Should have basic commands from Symfony Console
        $this->assertTrue($consoleApp->has('help'));
        $this->assertTrue($consoleApp->has('list'));

        // Should be able to find commands that exist
        $helpCommand = $consoleApp->find('help');
        $listCommand = $consoleApp->find('list');

        $this->assertNotNull($helpCommand);
        $this->assertNotNull($listCommand);
    }

    public function testMultipleInstancesAreIndependent(): void
    {
        $consoleApp1 = new ConsoleApplication();
        $consoleApp2 = new ConsoleApplication();

        $this->assertNotSame($consoleApp1, $consoleApp2);
        $this->assertInstanceOf(ConsoleApplication::class, $consoleApp1);
        $this->assertInstanceOf(ConsoleApplication::class, $consoleApp2);

        // Both should have the same commands but be different instances
        $this->assertEquals($consoleApp1->getName(), $consoleApp2->getName());
        $this->assertEquals($consoleApp1->getVersion(), $consoleApp2->getVersion());
    }

    public function testAllMethodsReturnExpectedTypes(): void
    {
        $consoleApp = new ConsoleApplication();

        $this->assertInstanceOf(ApplicationInterface::class, $consoleApp->getApp());
        $this->assertIsString($consoleApp->getName());
        $this->assertIsString($consoleApp->getVersion());
        $this->assertIsArray($consoleApp->all());
    }

    public function testConsoleApplicationInterfaceCompliance(): void
    {
        $reflectionClass = new \ReflectionClass(ConsoleApplication::class);
        $interfaces = $reflectionClass->getInterfaces();

        $this->assertArrayHasKey(ConsoleApplicationInterface::class, $interfaces);

        // Verify interface methods are implemented
        $interfaceReflection = new \ReflectionClass(ConsoleApplicationInterface::class);
        $interfaceMethods = $interfaceReflection->getMethods();

        foreach ($interfaceMethods as $method) {
            $this->assertTrue($reflectionClass->hasMethod($method->getName()));
        }
    }

    public function testSymfonyApplicationIntegration(): void
    {
        $consoleApp = new ConsoleApplication();

        // Should inherit Symfony Console Application functionality
        $this->assertTrue(method_exists($consoleApp, 'add'));
        $this->assertTrue(method_exists($consoleApp, 'find'));
        $this->assertTrue(method_exists($consoleApp, 'has'));
        $this->assertTrue(method_exists($consoleApp, 'all'));
    }

    public function testConsoleApplicationConfiguration(): void
    {
        $consoleApp = new ConsoleApplication();

        $this->assertEquals('PHP-OO Console', $consoleApp->getName());
        $this->assertEquals('1.0.0', $consoleApp->getVersion());

        // Should have help and list commands by default from Symfony
        $this->assertTrue($consoleApp->has('help'));
        $this->assertTrue($consoleApp->has('list'));
    }

    public function testApplicationInstanceConsistency(): void
    {
        $consoleApp = new ConsoleApplication();

        $app1 = $consoleApp->getApp();
        $app2 = $consoleApp->getApp();
        $app3 = $consoleApp->getApp();

        $this->assertSame($app1, $app2);
        $this->assertSame($app1, $app3);
        $this->assertSame($app2, $app3);
    }

    public function testCommandRegistration(): void
    {
        $consoleApp = new ConsoleApplication();

        $allCommands = $consoleApp->all();

        // Should have basic commands
        $this->assertTrue($consoleApp->has('help'));
        $this->assertTrue($consoleApp->has('list'));

        // Should have more than just the default commands
        $this->assertGreaterThan(2, count($allCommands));

        // Look for system namespace commands
        $systemNamespaceCommands = array_filter(array_keys($allCommands), function($commandName) {
            return strpos($commandName, 'system:') === 0;
        });

        // Should have at least one system command
        $this->assertGreaterThanOrEqual(1, count($systemNamespaceCommands));
    }

    public function testConsoleApplicationIsStateful(): void
    {
        $consoleApp1 = new ConsoleApplication();
        $consoleApp2 = new ConsoleApplication();

        // Each instance should have its own state but same configuration
        $this->assertEquals($consoleApp1->getName(), $consoleApp2->getName());
        $this->assertEquals($consoleApp1->getVersion(), $consoleApp2->getVersion());
        
        // But they are different instances
        $this->assertNotSame($consoleApp1, $consoleApp2);
        
        // And their apps may be the same (singleton pattern)
        $app1 = $consoleApp1->getApp();
        $app2 = $consoleApp2->getApp();
        
        $this->assertInstanceOf(ApplicationInterface::class, $app1);
        $this->assertInstanceOf(ApplicationInterface::class, $app2);
    }

    public function testConstructorInitializesCorrectly(): void
    {
        $consoleApp = new ConsoleApplication();

        // Constructor should set up name, version, and register commands
        $this->assertInstanceOf(ConsoleApplication::class, $consoleApp);
        $this->assertInstanceOf(ApplicationInterface::class, $consoleApp->getApp());
        $this->assertNotEmpty($consoleApp->all());
        $this->assertGreaterThan(2, count($consoleApp->all())); // At least help, list, and our commands
    }

    public function testApplicationGetter(): void
    {
        $consoleApp = new ConsoleApplication();

        $application = $consoleApp->getApp();

        $this->assertInstanceOf(ApplicationInterface::class, $application);

        // Multiple calls should return the same instance
        $this->assertSame($application, $consoleApp->getApp());
        $this->assertSame($application, $consoleApp->getApp());
    }

    public function testConsoleApplicationHasExpectedMethods(): void
    {
        $consoleApp = new ConsoleApplication();
        $reflection = new \ReflectionClass($consoleApp);

        // Should have our public methods
        $this->assertTrue($reflection->hasMethod('getApp'));
        $this->assertTrue($reflection->hasMethod('__construct'));

        // Should inherit Symfony methods
        $this->assertTrue($reflection->hasMethod('add'));
        $this->assertTrue($reflection->hasMethod('find'));
        $this->assertTrue($reflection->hasMethod('has'));
        $this->assertTrue($reflection->hasMethod('all'));
    }

    public function testConsoleApplicationFinalClass(): void
    {
        $reflection = new \ReflectionClass(ConsoleApplication::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testConstructorSetsUpCommandsProperly(): void
    {
        $consoleApp = new ConsoleApplication();

        // Basic verification that commands are registered
        $commands = $consoleApp->all();
        $commandNames = array_keys($commands);

        // Should have basic Symfony commands
        $this->assertContains('help', $commandNames);
        $this->assertContains('list', $commandNames);

        // Should have some system commands (at least one)
        $systemCommands = array_filter($commandNames, function($name) {
            return strpos($name, 'system:') === 0;
        });
        $this->assertGreaterThanOrEqual(1, count($systemCommands));
    }
}
