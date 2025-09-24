<?php

declare(strict_types=1);

namespace App\Application\Shared\Loader;

use App\Application\Shared\Registry\BootstrapRegistryInterface;
use Slim\App;

interface RouteLoaderInterface
{
    /**
     * Carrega todas as rotas dos bootstraps registrados na aplicação Slim
     */
    public function loadAllRoutes(BootstrapRegistryInterface $registry, App $app): void;
}
