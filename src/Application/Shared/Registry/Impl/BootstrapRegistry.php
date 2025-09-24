<?php

declare(strict_types=1);

namespace App\Application\Shared\Registry\Impl;

use App\Application\Shared\Registry\BootstrapRegistryInterface;
use App\Application\Shared\BootstrapInterface;

final class BootstrapRegistry implements BootstrapRegistryInterface
{
    /**
     * @var BootstrapInterface[]
     */
    private array $bootstraps = [];

    public function register(BootstrapInterface $bootstrap): void
    {
        $this->bootstraps[] = $bootstrap;
    }

    public function findByModule(string $moduleName): ?BootstrapInterface
    {
        foreach ($this->bootstraps as $bootstrap) {
            if ($bootstrap->belongsToModule($moduleName)) {
                return $bootstrap;
            }
        }

        return null;
    }

    /**
     * @return BootstrapInterface[]
     */
    public function getAll(): array
    {
        return $this->bootstraps;
    }

    /**
     * @return BootstrapInterface[]
     */
    public function getAllSortedByPriority(): array
    {
        $sorted = $this->bootstraps;
        
        // Ordena por prioridade usando Tell Don't Ask
        usort($sorted, function (BootstrapInterface $a, BootstrapInterface $b) {
            if ($a->hasPriorityOver($b)) {
                return -1; // a vem antes de b
            }
            if ($b->hasPriorityOver($a)) {
                return 1;  // b vem antes de a
            }
            return 0; // mesma prioridade
        });

        return $sorted;
    }
}
