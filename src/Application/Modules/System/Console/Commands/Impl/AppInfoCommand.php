<?php

declare(strict_types=1);

namespace App\Application\Modules\System\Console\Commands\Impl;

use App\Application\Impl\ApiApplication;
use App\Application\Modules\System\Console\Commands\CommandInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class AppInfoCommand extends Command implements CommandInterface
{
    protected static $defaultDescription = 'Mostrar informações da aplicação';
    protected static $defaultName = 'system:app:info';

    public function getCommand(): Command
    {
        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Informações da Aplicação PHP-OO');

        // Informações do PHP
        $io->section('Informações do PHP');
        $io->table(
            ['Propriedade', 'Valor'],
            [
                ['Versão PHP', phpversion()],
                ['SAPI', php_sapi_name()],
                ['Limite de Memória', ini_get('memory_limit')],
                ['Tempo Máximo de Execução', ini_get('max_execution_time') . 's'],
                ['Extensões Carregadas', count(get_loaded_extensions())],
            ]
        );

        // Informações da Aplicação
        $io->section('Informações da Aplicação');
        $app = ApiApplication::getInstance();
        $container = $app->container();

        $io->table(
            ['Propriedade', 'Valor'],
            [
                ['Nome da App', 'Projeto de Treinamento PHP-OO'],
                ['Versão da App', '1.0.0'],
                ['Classe do Container', get_class($container)],
                ['Serviços Registrados', 'Múltiplos (ver container)'],
            ]
        );

        // Informações do Ambiente
        $io->section('Informações do Ambiente');
        $io->table(
            ['Propriedade', 'Valor'],
            [
                ['Diretório de Trabalho', getcwd()],
                ['Caminho do Script', __FILE__],
                ['Caminho Vendor', realpath(__DIR__ . '/../../vendor')],
                ['Caminho Cache', realpath(__DIR__ . '/../../cache')],
                ['Ambiente Docker', getenv('DOCKER') ? 'Sim' : 'Não'],
            ]
        );

        // Verificação de Extensões
        $io->section('Extensões Obrigatórias');
        $requiredExtensions = ['pdo', 'pdo_pgsql', 'json', 'mbstring'];
        $extensionStatus = [];

        foreach ($requiredExtensions as $ext) {
            $loaded = extension_loaded($ext);
            $extensionStatus[] = [
                $ext,
                $loaded ? '✅ Carregada' : '❌ Não Carregada',
            ];
        }

        $io->table(['Extensão', 'Status'], $extensionStatus);

        return Command::SUCCESS;
    }
}
