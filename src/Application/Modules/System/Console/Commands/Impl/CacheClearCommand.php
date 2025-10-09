<?php

declare(strict_types=1);

namespace App\Application\Modules\System\Console\Commands\Impl;

use App\Application\Modules\System\Console\Commands\CommandInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CacheClearCommand extends Command implements CommandInterface
{
    protected static $defaultDescription = 'Clear application cache';
    protected static $defaultName = 'system:cache:clear';

    public function getCommand(): Command
    {
        return $this;
    }

    protected function configure(): void
    {
        $this->addOption('all', 'a', InputOption::VALUE_NONE, 'Clear all cache types');
        $this->addOption('di', null, InputOption::VALUE_NONE, 'Clear DI container cache only');
        $this->addOption('compiled', null, InputOption::VALUE_NONE, 'Clear compiled container only');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $all = $input->getOption('all');
        $di = $input->getOption('di');
        $compiled = $input->getOption('compiled');

        if (! $all && ! $di && ! $compiled) {
            $all = true; // Padrão para limpar tudo
        }

        $io->title('Cache Clear');

        $cleared = [];

        if ($all || $compiled) {
            $this->clearCompiledContainer($io, $cleared);
        }

        if ($all || $di) {
            $this->clearDICache($io, $cleared);
        }

        if (empty($cleared)) {
            $io->warning('No cache to clear');
        } else {
            $io->success('Cache cleared: ' . implode(', ', $cleared));
        }

        return Command::SUCCESS;
    }

    private function clearCompiledContainer(SymfonyStyle $io, array &$cleared): void
    {
        $compiledFile = __DIR__ . '/../../cache/CompiledContainer.php';

        if (file_exists($compiledFile)) {
            if (unlink($compiledFile)) {
                $cleared[] = 'Compiled Container';
                $io->text('✅ Compiled container cache cleared');
            } else {
                $io->error('❌ Failed to clear compiled container cache');
            }
        } else {
            $io->text('ℹ️  No compiled container cache found');
        }
    }

    private function clearDICache(SymfonyStyle $io, array &$cleared): void
    {
        // Limpar cache APCu se disponível
        if (function_exists('apcu_clear_cache')) {
            if (apcu_clear_cache()) {
                $cleared[] = 'DI Cache (APCu)';
                $io->text('✅ DI cache (APCu) cleared');
            } else {
                $io->error('❌ Failed to clear DI cache (APCu)');
            }
        } else {
            $io->text('ℹ️  APCu not available, skipping DI cache clear');
        }
    }
}
