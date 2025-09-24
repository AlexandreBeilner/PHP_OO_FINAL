<?php

declare(strict_types=1);

namespace App\Domain\Security\Services;

use App\Domain\Security\Entities\UserEntityInterface;
use App\Domain\Auth\DTOs\Impl\LoginDataDTO;
use App\Domain\Auth\DTOs\Impl\ChangePasswordDataDTO;

interface AuthServiceInterface
{
    /**
     * Autentica um usuário (método legado)
     */
    public function authenticateWithResponse(string $email, string $password): array;

    /**
     * Autentica usando DTO puro (SRP + Tell Don't Ask)
     */
    public function authenticate(LoginDataDTO $credentials): ?UserEntityInterface;

    /**
     * Altera senha usando DTO puro (SRP + Tell Don't Ask)
     */
    public function changePassword(ChangePasswordDataDTO $data): ?UserEntityInterface;

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
