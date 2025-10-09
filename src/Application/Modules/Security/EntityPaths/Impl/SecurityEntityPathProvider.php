<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\EntityPaths\Impl;

use App\Application\Shared\EntityPaths\EntityPathProviderInterface;
use App\Application\Shared\Utils\Impl\ProjectRootDiscovery;

/**
 * Provedor de entity paths específico do módulo Security
 *
 * SRP: Responsabilidade única de fornecer paths do módulo Security
 * Object Calisthenics: Uma variável de instância, Tell Don't Ask
 */
final class SecurityEntityPathProvider implements EntityPathProviderInterface
{
    private string $securityEntitiesPath;

    /**
     * DI: Injeta dependência via construtor
     * Object Calisthenics: Construtor focado, uma responsabilidade
     */
    public function __construct()
    {
        $this->securityEntitiesPath = $this->buildSecurityEntityPath();
    }

    /**
     * Tell Don't Ask: Retorna paths do módulo Security
     * Object Calisthenics: Um nível de indentação
     */
    public function getEntityPaths(): array
    {
        if (! $this->hasEntityPaths()) {
            return [];
        }

        return [$this->securityEntitiesPath];
    }

    /**
     * Tell Don't Ask: Informa se tem paths (sempre true para Security)
     * Object Calisthenics: Não usar else
     */
    public function hasEntityPaths(): bool
    {
        return $this->isSecurityEntityPathValid();
    }

    /**
     * SRP: Constrói path das entidades Security
     * Object Calisthenics: Um nível de indentação, método privado focado
     */
    private function buildSecurityEntityPath(): string
    {
        return ProjectRootDiscovery::getProjectRoot() . '/src/Domain/Security/Entities/Impl';
    }

    /**
     * SRP: Valida se path existe
     * Object Calisthenics: Método privado com uma responsabilidade
     */
    private function isSecurityEntityPathValid(): bool
    {
        return is_dir($this->securityEntitiesPath);
    }
}
