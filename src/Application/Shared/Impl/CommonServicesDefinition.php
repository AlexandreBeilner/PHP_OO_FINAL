<?php

declare(strict_types=1);

namespace App\Application\Shared\Impl;

use App\Application\Shared\ServiceDefinitionInterface;
use App\Domain\Common\Entities\Behaviors\Impl\SoftDeletableBehavior;
use App\Domain\Common\Entities\Behaviors\Impl\TimestampableBehavior;
use App\Domain\Common\Entities\Behaviors\Impl\UuidableBehavior;
use App\Domain\Common\Validators\Impl\EmailValidator;
use DI\ContainerBuilder;
use function DI\autowire;

final class CommonServicesDefinition implements ServiceDefinitionInterface
{
    /**
     * Definições de serviços comuns para o container DI
     * 
     * Commands usam Factory Pattern via ValidationServices
     * Entity Behaviors são registrados para uso interno nas entidades
     */
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            // Validators - Serviços de validação de entrada
            EmailValidator::class => autowire(EmailValidator::class),

            // Entity Behaviors - Padrão comportamental
            TimestampableBehavior::class => autowire(TimestampableBehavior::class),
            SoftDeletableBehavior::class => autowire(SoftDeletableBehavior::class),
            UuidableBehavior::class => autowire(UuidableBehavior::class),
        ]);
    }
}
