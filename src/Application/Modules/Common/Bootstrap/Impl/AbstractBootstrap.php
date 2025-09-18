<?php

declare(strict_types=1);

namespace App\Application\Modules\Common\Bootstrap\Impl;

use App\Application\Modules\Common\Bootstrap\BootstrapInterface;
use DI\ContainerBuilder;

abstract class AbstractBootstrap implements BootstrapInterface
{
    protected function loadServiceDefinitions(ContainerBuilder $builder, array $definitions): void
    {
        foreach ($definitions as $definition) {
            if (is_string($definition) && class_exists($definition)) {
                $instance = new $definition();
                if (method_exists($instance, 'register')) {
                    $instance->register($builder);
                }
            }
        }
    }

    public function getPriority(): int
    {
        return 100; // Prioridade padrão
    }
}
