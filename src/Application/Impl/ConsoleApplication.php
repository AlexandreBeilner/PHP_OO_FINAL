<?php

declare(strict_types=1);

namespace App\Application\Impl;

use App\Application\ApplicationInterface;
use App\Application\ConsoleApplicationInterface;
use App\Application\Modules\System\Console\Commands\Impl\AppInfoCommand;
use App\Application\Modules\System\Console\Commands\Impl\CacheClearCommand;
use App\Application\Modules\System\Console\Commands\Impl\DatabaseTestCommand;
use App\Application\Modules\System\Console\Commands\Impl\DoctrineTestCommand;
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

        // Comandos de módulos (serão adicionados pelos alunos)
        $this->addModuleCommands();
    }
}
