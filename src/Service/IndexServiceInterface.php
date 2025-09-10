<?php

namespace App\Service;

interface IndexServiceInterface
{
    public function welcomeMessage(): string;
    public function phpVersion(): string;
    public function sessionInfo(): array;
}
