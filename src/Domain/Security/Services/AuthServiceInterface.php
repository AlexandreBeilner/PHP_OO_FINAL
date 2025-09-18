<?php

declare(strict_types=1);

namespace App\Domain\Security\Services;

interface AuthServiceInterface
{
    /**
     * Autentica um usuário
     */
    public function authenticate(string $email, string $password): array;

    /**
     * Extrai ID da URL
     */
    public function extractIdFromUrl(string $url): ?int;

    /**
     * Gera token de autenticação
     */
    public function generateToken(array $userData): string;

    /**
     * Valida dados de autenticação
     */
    public function validateAuthData(array $data): array;

    /**
     * Valida token de autenticação
     */
    public function validateToken(string $token): array;
}
