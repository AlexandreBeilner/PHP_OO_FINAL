<?php

declare(strict_types=1);

namespace App\Domain\Common\Validators\Impl;

use App\Domain\Common\Validators\CnpjValidatorInterface;

final class CnpjValidator implements CnpjValidatorInterface
{
    private string $errorMessage = 'Formato de CNPJ inválido';

    public function clean(string $cnpj): string
    {
        return preg_replace('/[^0-9]/', '', $cnpj);
    }

    public function format(string $cnpj): string
    {
        $cnpj = $this->clean($cnpj);
        return substr($cnpj, 0, 2) . '.' .
            substr($cnpj, 2, 3) . '.' .
            substr($cnpj, 5, 3) . '/' .
            substr($cnpj, 8, 4) . '-' .
            substr($cnpj, 12, 2);
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function validate(string $cnpj): bool
    {
        // Remove caracteres não numéricos
        $cnpj = $this->clean($cnpj);

        if (strlen($cnpj) !== 14) {
            $this->errorMessage = 'CNPJ deve ter 14 dígitos';
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            $this->errorMessage = 'CNPJ não pode ter todos os dígitos iguais';
            return false;
        }

        // Validação do primeiro dígito verificador
        $sum = 0;
        $weight = 5;
        for ($i = 0; $i < 12; $i++) {
            $sum += intval($cnpj[$i]) * $weight;
            $weight = ($weight === 2) ? 9 : $weight - 1;
        }
        $remainder = $sum % 11;
        $firstDigit = ($remainder < 2) ? 0 : 11 - $remainder;

        if (intval($cnpj[12]) !== $firstDigit) {
            $this->errorMessage = 'Primeiro dígito verificador do CNPJ inválido';
            return false;
        }

        // Validação do segundo dígito verificador
        $sum = 0;
        $weight = 6;
        for ($i = 0; $i < 13; $i++) {
            $sum += intval($cnpj[$i]) * $weight;
            $weight = ($weight === 2) ? 9 : $weight - 1;
        }
        $remainder = $sum % 11;
        $secondDigit = ($remainder < 2) ? 0 : 11 - $remainder;

        if (intval($cnpj[13]) !== $secondDigit) {
            $this->errorMessage = 'Segundo dígito verificador do CNPJ inválido';
            return false;
        }

        return true;
    }
}
