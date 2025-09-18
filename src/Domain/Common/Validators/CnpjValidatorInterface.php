<?php

declare(strict_types=1);

namespace App\Domain\Common\Validators;

interface CnpjValidatorInterface
{
    public function clean(string $cnpj): string;

    public function format(string $cnpj): string;

    public function validate(string $cnpj): bool;
}
