<?php

declare(strict_types=1);

namespace App\Application\Modules\Common\Bootstrap\Impl;

use App\Application\Modules\Common\Bootstrap\ServiceDefinitionInterface;
use App\Domain\Common\Entities\Behaviors\Impl\SoftDeletableBehavior;
use App\Domain\Common\Entities\Behaviors\Impl\TimestampableBehavior;
use App\Domain\Common\Entities\Behaviors\Impl\UuidableBehavior;
use App\Domain\Common\Validators\Impl\CnpjValidator;
use App\Domain\Common\Validators\Impl\CpfValidator;
use App\Domain\Common\Validators\Impl\EmailValidator;
use DI\ContainerBuilder;
use function DI\autowire;

final class CommonServicesDefinition implements ServiceDefinitionInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            // Validators
            EmailValidator::class => autowire(EmailValidator::class),
            CpfValidator::class => autowire(CpfValidator::class),
            CnpjValidator::class => autowire(CnpjValidator::class),

            // Behaviors
            TimestampableBehavior::class => autowire(TimestampableBehavior::class),
            SoftDeletableBehavior::class => autowire(SoftDeletableBehavior::class),
            UuidableBehavior::class => autowire(UuidableBehavior::class),
        ]);
    }
}
