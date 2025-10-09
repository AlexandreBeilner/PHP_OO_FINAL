<?php

declare(strict_types=1);

namespace App\Application\Modules\Auth\Bootstrap\Impl;

use App\Application\Modules\System\Bootstrap\Impl\SystemBootstrap;
use App\Application\Modules\Auth\Http\Routing\AuthRouteProvider;
use App\Application\Modules\Security\Bootstrap\Impl\AuthControllerDefinition;
use App\Application\Modules\Security\Bootstrap\Impl\AuthDataValidatorDefinition;
use App\Application\Modules\Security\Bootstrap\Impl\AuthServiceDefinition;
use App\Application\Modules\Security\Bootstrap\Impl\AuthValidationServiceDefinition;
use App\Application\Shared\BootstrapInterface;
use App\Application\Shared\Http\Routing\RouteProviderFactoryInterface;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use App\Application\Shared\Impl\AbstractBootstrap;
use DI\ContainerBuilder;
use App\Application\Shared\Impl\CommonBootstrap;

/**
 * Bootstrap do módulo de Autenticação
 *
 * Responsável por registrar todos os serviços relacionados à autenticação:
 * - Serviços de autenticação (login, logout, etc.)
 * - Validação de dados de autenticação
 * - Controllers de autenticação
 */
final class AuthBootstrap extends AbstractBootstrap implements BootstrapInterface
{
    private ?RouteProviderFactoryInterface $routeProviderFactory;

    public function __construct(?RouteProviderFactoryInterface $routeProviderFactory = null)
    {
        $this->routeProviderFactory = $routeProviderFactory;
    }

    public function belongsToModule(string $moduleName): bool
    {
        return $moduleName === 'Auth';
    }

    public function getModuleName(): string
    {
        return 'Auth';
    }

    public function getPriority(): int
    {
        return 25; // Prioridade entre System (20) e Security (30)
    }

    public function getRouteProvider(): ?RouteProviderInterface
    {
        if ($this->routeProviderFactory !== null) {
            return $this->routeProviderFactory->createAuthRouteProvider();
        }

        // Fallback para compatibilidade (viola DIP, mas mantém funcionalidade)
        return new \App\Application\Modules\Auth\Http\Routing\Impl\AuthRouteProvider();
    }

    public function hasPriorityOver(BootstrapInterface $other): bool
    {
        // Auth tem prioridade 25
        if ($other instanceof CommonBootstrap) {
            return false; // Common (10) tem prioridade maior
        }
        if ($other instanceof SystemBootstrap) {
            return false; // System (20) tem prioridade maior
        }
        return true; // Tem prioridade sobre Security (30), Application (50) e outros
    }

    public function hasRoutes(): bool
    {
        return true;
    }

    public function register(ContainerBuilder $builder): void
    {
        $definitions = [
            // Domain Services - Autenticação
            AuthServiceDefinition::class,

            // Validation Services - Validação de dados de auth
            AuthValidationServiceDefinition::class,

            // Input Validators - Validadores básicos
            AuthDataValidatorDefinition::class,

            // HTTP Controllers - Controllers de auth
            AuthControllerDefinition::class,
        ];

        $this->loadServiceDefinitions($builder, $definitions);
    }
}
