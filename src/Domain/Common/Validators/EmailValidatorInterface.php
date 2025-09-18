<?php

declare(strict_types=1);

namespace App\Domain\Common\Validators;

interface EmailValidatorInterface extends ValidatorInterface
{
    public function isValidFormat(string $email): bool;

    public function normalize(string $email): string;

    public function validate($value): bool;
}
