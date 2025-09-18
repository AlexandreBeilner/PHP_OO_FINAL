<?php

declare(strict_types=1);

namespace App\Application\Common\DTOs\User;

interface UpdateUserRequestDTOInterface
{
    public function getName(): ?string;
    public function getEmail(): ?string;
    public function getPassword(): ?string;
    public function getRole(): ?string;
    public function getStatus(): ?string;
}
