<?php

declare(strict_types=1);

namespace App\Domain\Common\Validators;

interface ValidatorInterface
{
    public function getErrorMessage(): string;

    public function validate($value): bool;
}
