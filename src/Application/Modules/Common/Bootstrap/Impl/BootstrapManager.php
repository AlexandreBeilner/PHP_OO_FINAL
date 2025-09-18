<?php

declare(strict_types=1);

namespace App\Application\Modules\Common\Bootstrap\Impl;

use App\Application\Modules\Common\Bootstrap\BootstrapInterface;
use DI\ContainerBuilder;

final class BootstrapManager
{
    /**
     * @var BootstrapInterface[]
     */
    private array $bootstraps = [];

    public function __construct()
    {
        $this->registerBootstrap(new CommonBootstrap());
        $this->registerBootstrap(new \App\Application\Modules\System\Bootstrap\Impl\SystemBootstrap());
        $this->registerBootstrap(new \App\Application\Modules\Security\Bootstrap\Impl\SecurityBootstrap());
        $this->registerBootstrap(new ApplicationBootstrap());
    }

    public function registerBootstrap(BootstrapInterface $bootstrap): void
    {
        $this->bootstraps[] = $bootstrap;
    }

    public function loadAll(ContainerBuilder $builder): void
    {
        // Ordena por prioridade (menor número = maior prioridade)
        usort($this->bootstraps, function (BootstrapInterface $a, BootstrapInterface $b) {
            return $a->getPriority() <=> $b->getPriority();
        });

        foreach ($this->bootstraps as $bootstrap) {
            $bootstrap->register($builder);
        }
    }

    /**
     * @return BootstrapInterface[]
     */
    public function getBootstraps(): array
    {
        return $this->bootstraps;
    }

    public function getBootstrapByModule(string $moduleName): ?BootstrapInterface
    {
        foreach ($this->bootstraps as $bootstrap) {
            if ($bootstrap->getModuleName() === $moduleName) {
                return $bootstrap;
            }
        }

        return null;
    }
}
