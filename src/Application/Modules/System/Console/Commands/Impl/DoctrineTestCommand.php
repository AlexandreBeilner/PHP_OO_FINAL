<?php

declare(strict_types=1);

namespace App\Application\Modules\System\Console\Commands\Impl;

use App\Application\Impl\ApiApplication;
use App\Application\Modules\System\Console\Commands\CommandInterface;
use App\Common\Database\DoctrineEntityManagerInterface;
use App\Domain\Security\Entities\Impl\UserEntity;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DoctrineTestCommand extends Command implements CommandInterface
{
    protected static $defaultDescription = 'Testar conexões e funcionalidades do Doctrine ORM';
    protected static $defaultName = 'system:doctrine:test';

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

            $io->title('Teste de Conexão Doctrine ORM');

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

            // Testar Seleção de EntityManager
            $io->section('Teste de Seleção de EntityManager');
            try {
                $readEm = $doctrineManager->getEntityManager('read');
                $writeEm = $doctrineManager->getEntityManager('write');
                $selectEm = $doctrineManager->getEntityManagerForQuery('SELECT');
                $insertEm = $doctrineManager->getEntityManagerForQuery('INSERT');

                $io->success("✅ EntityManager de Leitura: " . get_class($readEm));
                $io->success("✅ EntityManager de Escrita: " . get_class($writeEm));
                $io->success("✅ EntityManager para SELECT: " . get_class($selectEm));
                $io->success("✅ EntityManager para INSERT: " . get_class($insertEm));
            } catch (Exception $e) {
                $io->error("❌ Erro na Seleção de EntityManager: " . $e->getMessage());
            }

            // Mostrar Estatísticas de Conexão
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

            // Testar Mapeamento de Entidade
            $io->section('Teste de Mapeamento de Entidade');
            try {
                $master = $doctrineManager->getMaster();
                $metadata = $master->getClassMetadata(\App\Domain\Security\Entities\Impl\UserEntity::class);
                $io->success("✅ Entidade User mapeada com sucesso");
                $io->text("Tabela: " . $metadata->getTableName());
                $io->text("Colunas: " . implode(', ', array_keys($metadata->getColumnNames())));
            } catch (Exception $e) {
                $io->error("❌ Erro no Mapeamento de Entidade: " . $e->getMessage());
            }

            return Command::SUCCESS;

        } catch (Exception $e) {
            $io->error('Falha ao testar conexões Doctrine: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
