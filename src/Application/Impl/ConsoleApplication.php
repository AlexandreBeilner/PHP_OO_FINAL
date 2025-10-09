<?php

declare(strict_types=1);

namespace App\Application\Impl;

use App\Application\ApplicationInterface;
use App\Application\ConsoleApplicationInterface;
use App\Application\Modules\System\Console\Commands\Impl\AppInfoCommand;
use App\Application\Modules\System\Console\Commands\Impl\CacheClearCommand;
use App\Application\Modules\System\Console\Commands\Impl\DatabaseTestCommand;
use App\Application\Modules\System\Console\Commands\Impl\DoctrineTestCommand;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\CurrentCommand;
use Doctrine\Migrations\Tools\Console\Command\DumpSchemaCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\ListCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\RollupCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Exception;
use Symfony\Component\Console\Application as SymfonyApplication;

final class ConsoleApplication extends SymfonyApplication implements ConsoleApplicationInterface
{
    private ApplicationInterface $app;

    public function __construct()
    {
        parent::__construct('PHP-OO Console', '1.0.0');

        $this->app = ApiApplication::getInstance();
        $this->registerCommands();
    }

    public function getApp(): ApplicationInterface
    {
        return $this->app;
    }

    private function addMigrationsCommands(): void
    {
        try {
            // Obter DependencyFactory das migrations via DI
            $dependencyFactory = $this->app->container()->get(DependencyFactory::class);

            // Comandos nativos do Doctrine Migrations
            $this->add(new CurrentCommand($dependencyFactory));
            $this->add(new DumpSchemaCommand($dependencyFactory));
            $this->add(new ExecuteCommand($dependencyFactory));
            $this->add(new GenerateCommand($dependencyFactory));
            $this->add(new LatestCommand($dependencyFactory));
            $this->add(new ListCommand($dependencyFactory));
            $this->add(new MigrateCommand($dependencyFactory));
            $this->add(new RollupCommand($dependencyFactory));
            $this->add(new StatusCommand($dependencyFactory));
            $this->add(new SyncMetadataCommand($dependencyFactory));
            $this->add(new UpToDateCommand($dependencyFactory));
            $this->add(new VersionCommand($dependencyFactory));

        } catch (Exception $e) {
            // Se não conseguir carregar migrations, continua sem os comandos
            // Permite que a aplicação funcione mesmo com problemas na configuração
        }
    }

    private function addModuleCommands(): void
    {
        // Aqui os alunos podem adicionar comandos específicos de seus módulos
        // Exemplo:
        // $this->add(new Commands\Users\CreateUserCommand());
        // $this->add(new Commands\Products\CreateProductCommand());
    }

    private function registerCommands(): void
    {
        // Comandos básicos do sistema
        $this->add(new DatabaseTestCommand());
        $this->add(new DoctrineTestCommand());
        $this->add(new CacheClearCommand());
        $this->add(new AppInfoCommand());

        // Comandos nativos do Doctrine Migrations
        $this->addMigrationsCommands();

        // Comandos de módulos (serão adicionados pelos alunos)
        $this->addModuleCommands();
    }
}
