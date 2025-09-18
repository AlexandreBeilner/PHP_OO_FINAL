<?php

declare(strict_types=1);

namespace App\Application\Impl;

use App\Application\ApplicationInterface;
use App\Application\Common\Http\Impl\SlimAppFactory;
use App\Application\Common\Http\SlimAppFactoryInterface;
use App\Application\Modules\Common\Bootstrap\Impl\BootstrapManager;
use DI\Container;
use DI\ContainerBuilder;
use Slim\App;

final class ApiApplication implements ApplicationInterface
{
    private Container $container;
    private static ?ApiApplication $instance = null;
    private ?SlimAppFactoryInterface $slimAppFactory = null;

    private function __construct()
    {
        $this->initializeContainer();
    }

    public function __wakeup()
    {
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function createSlimApp(): App
    {
        return $this->getSlimAppFactory()->create();
    }

    public static function getInstance(): ApplicationInterface
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getSlimAppFactory(): SlimAppFactoryInterface
    {
        if ($this->slimAppFactory === null) {
            $this->slimAppFactory = new SlimAppFactory($this);
        }

        return $this->slimAppFactory;
    }

    private function __clone()
    {
    }

    // Prevenir clonagem

    private function initializeContainer(): void
    {
        $builder = new ContainerBuilder();

        // Habilitar compilação
        $builder->enableCompilation(__DIR__ . '/../../../cache');

        // Cache de definições desabilitado (APCu não disponível)
        // $builder->enableDefinitionCache();

        // Carregar definições dos serviços usando BootstrapManager
        $bootstrapManager = new BootstrapManager();
        $bootstrapManager->loadAll($builder);

        $this->container = $builder->build();
    }

    // Prevenir unserialize
}
