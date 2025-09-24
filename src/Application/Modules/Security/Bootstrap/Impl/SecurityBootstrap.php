<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\Bootstrap\Impl;

use App\Application\Shared\BootstrapInterface;
use App\Application\Shared\Impl\AbstractBootstrap;
use App\Application\Shared\Http\Routing\RouteProviderInterface;
use App\Application\Shared\Http\Routing\RouteProviderFactoryInterface;
use App\Application\Modules\Security\Http\Routing\SecurityRouteProvider;
use DI\ContainerBuilder;

final class SecurityBootstrap extends AbstractBootstrap implements BootstrapInterface
{
    private ?RouteProviderFactoryInterface $routeProviderFactory;

    public function __construct(?RouteProviderFactoryInterface $routeProviderFactory = null)
    {
        $this->routeProviderFactory = $routeProviderFactory;
    }

    public function getModuleName(): string
    {
        return 'Security';
    }

    public function belongsToModule(string $moduleName): bool
    {
        return $moduleName === 'Security';
    }

    public function getPriority(): int
    {
        return 30; // Prioridade baixa - depende de Common
    }

    public function hasPriorityOver(BootstrapInterface $other): bool
    {
        // Security tem prioridade 30  
        if ($other instanceof \App\Application\Shared\Impl\CommonBootstrap) {
            return false; // Common (10) tem prioridade maior
        }
        if ($other instanceof \App\Application\Modules\System\Bootstrap\Impl\SystemBootstrap) {
            return false; // System (20) tem prioridade maior
        }
        if ($other instanceof \App\Application\Modules\Auth\Bootstrap\Impl\AuthBootstrap) {
            return false; // Auth (25) tem prioridade maior
        }
        return true; // Tem prioridade sobre Application (50) e outros
    }

    /**
     * Registro do módulo de segurança (usuários)
     * 
     * Todos os serviços implementam padrão Tell, Don't Ask
     * Commands usam Factory Pattern e não são registrados no container DI
     * ValidationServices criam Commands sob demanda via métodos factory fromArray()
     * 
     * Nota: Serviços de autenticação foram movidos para o módulo Auth
     */
    public function register(ContainerBuilder $builder): void
    {
        $definitions = [
            // Domain Services - Padrão Tell, Don't Ask com Entity Behaviors
            UserServiceDefinition::class,
            
            // Validation Services - Cria Commands via Factory Pattern
            UserValidationServiceDefinition::class,

            // Input Validators - Serviços básicos de validação
            UserDataValidatorDefinition::class,

            // HTTP Controllers - Padrão Command integrado
            UserControllerDefinition::class,
        ];

        $this->loadServiceDefinitions($builder, $definitions);
    }

    public function hasRoutes(): bool
    {
        return true;
    }

    public function getRouteProvider(): ?RouteProviderInterface
    {
        if ($this->routeProviderFactory !== null) {
            return $this->routeProviderFactory->createSecurityRouteProvider();
        }
        
        // Fallback para compatibilidade (viola DIP, mas mantém funcionalidade)
        return new SecurityRouteProvider();
    }
}
