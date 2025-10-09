<?php

declare(strict_types=1);

namespace App\Domain\Security\Services;

use App\Domain\Auth\DTOs\Impl\ChangePasswordDataDTO;
use App\Domain\Auth\DTOs\Impl\LoginDataDTO;
use App\Domain\Security\Entities\UserEntityInterface;

interface AuthServiceInterface
{
    /**
     * Autentica usando DTO puro (SRP + Tell Don't Ask)
     */
    public function authenticate(LoginDataDTO $credentials): ?UserEntityInterface;

    /**
     * Autentica um usuário (método legado)
     */
    public function authenticateWithResponse(string $email, string $password): array;

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
