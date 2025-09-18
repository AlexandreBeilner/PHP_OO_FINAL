<?php

declare(strict_types=1);

namespace App\Domain\Common\Validators\Impl;

use App\Domain\Common\Validators\CpfValidatorInterface;

final class CpfValidator implements CpfValidatorInterface
{
    private string $errorMessage = 'Formato de CPF inválido';

    public function clean(string $cpf): string
    {
        return preg_replace('/[^0-9]/', '', $cpf);
    }

    public function format(string $cpf): string
    {
        $cpf = $this->clean($cpf);
        return substr($cpf, 0, 3) . '.' .
            substr($cpf, 3, 3) . '.' .
            substr($cpf, 6, 3) . '-' .
            substr($cpf, 9, 2);
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function validate(string $cpf): bool
    {
        // Remove caracteres não numéricos
        $cpf = $this->clean($cpf);

        if (strlen($cpf) !== 11) {
            $this->errorMessage = 'CPF deve ter 11 dígitos';
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            $this->errorMessage = 'CPF não pode ter todos os dígitos iguais';
            return false;
        }

        // Validação do primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $remainder = $sum % 11;
        $firstDigit = ($remainder < 2) ? 0 : 11 - $remainder;

        if (intval($cpf[9]) !== $firstDigit) {
            $this->errorMessage = 'Primeiro dígito verificador do CPF inválido';
            return false;
        }

        // Validação do segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $remainder = $sum % 11;
        $secondDigit = ($remainder < 2) ? 0 : 11 - $remainder;

        if (intval($cpf[10]) !== $secondDigit) {
            $this->errorMessage = 'Segundo dígito verificador do CPF inválido';
            return false;
        }

        return true;
    }
}
