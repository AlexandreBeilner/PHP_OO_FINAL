<?php

declare(strict_types=1);

namespace App\Domain\Common\Validators;

interface CpfValidatorInterface
{
    public function clean(string $cpf): string;

    public function format(string $cpf): string;

    public function validate(string $cpf): bool;
}
