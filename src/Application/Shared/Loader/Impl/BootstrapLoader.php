<?php

declare(strict_types=1);

namespace App\Application\Shared\Loader\Impl;

use App\Application\Shared\Loader\BootstrapLoaderInterface;
use App\Application\Shared\Registry\BootstrapRegistryInterface;
use DI\ContainerBuilder;

final class BootstrapLoader implements BootstrapLoaderInterface
{
    public function loadAll(BootstrapRegistryInterface $registry, ContainerBuilder $builder): void
    {
        $bootstraps = $registry->getAllSortedByPriority();

        foreach ($bootstraps as $bootstrap) {
            $bootstrap->register($builder);
        }
    }
}
