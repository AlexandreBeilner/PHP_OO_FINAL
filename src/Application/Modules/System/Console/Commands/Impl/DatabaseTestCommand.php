<?php

declare(strict_types=1);

namespace App\Application\Modules\System\Console\Commands\Impl;

use App\Application\Impl\ApiApplication;
use App\Application\Modules\System\Console\Commands\CommandInterface;
use App\Common\Database\DoctrineEntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DatabaseTestCommand extends Command implements CommandInterface
{
    protected static $defaultDescription = 'Testar conexões de banco de dados';
    protected static $defaultName = 'system:database:test';

    public function __construct()
    {
        parent::__construct();
    }

    public function getCommand(): Command
    {
        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $app = ApiApplication::getInstance();
        $container = $app->container();

        try {
            $doctrineManager = $container->get(DoctrineEntityManagerInterface::class);

            $io->title('Teste de Conexão de Banco de Dados (Legado)');
            $io->note('Este comando agora usa o novo sistema Doctrine ORM');

            // Testar Conexão Master
            $io->section('Conexão Master (Escrita)');
            try {
                $master = $doctrineManager->getMaster();
                $result = $master->getConnection()->fetchAssociative("SELECT 'Master DB' as db_type, version() as version");
                $io->success("✅ Master: " . $result['db_type'] . " - " . $result['version']);
            } catch (Exception $e) {
                $io->error("❌ Erro Master: " . $e->getMessage());
            }

            // Testar Conexão Slave
            $io->section('Conexão Slave (Leitura)');
            try {
                $slave = $doctrineManager->getSlave();
                $result = $slave->getConnection()->fetchAssociative("SELECT 'Slave DB' as db_type, version() as version");
                $io->success("✅ Slave: " . $result['db_type'] . " - " . $result['version']);
            } catch (Exception $e) {
                $io->error("❌ Erro Slave: " . $e->getMessage());
            }

            // Mostrar Estatísticas
            $io->section('Estatísticas de Conexão');
            $stats = $doctrineManager->getConnectionStats();
            $io->table(
                ['Propriedade', 'Valor'],
                [
                    ['Master Conectado', $stats['master_connected'] ? 'Sim' : 'Não'],
                    ['Slave Conectado', $stats['slave_connected'] ? 'Sim' : 'Não'],
                    ['Driver Master', $stats['master_driver']],
                    ['Driver Slave', $stats['slave_driver']],
                ]
            );

            return Command::SUCCESS;

        } catch (Exception $e) {
            $io->error('Falha ao testar conexões de banco de dados: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
