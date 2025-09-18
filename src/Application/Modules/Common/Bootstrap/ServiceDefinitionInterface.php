<?php

declare(strict_types=1);

namespace App\Application\Modules\Common\Bootstrap;

use DI\ContainerBuilder;

interface ServiceDefinitionInterface
{
    /**
     * Registra as definições de serviços no container DI
     */
    public function register(ContainerBuilder $builder): void;
}
