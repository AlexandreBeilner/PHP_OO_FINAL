<?php

declare(strict_types=1);

namespace App\Domain\System\Services;

interface SystemResponseServiceInterface
{
    public function buildEnvironmentInfo(): array;

    public function buildSystemInfoResponse(array $systemInfo): array;
}
