<?php

declare(strict_types=1);

namespace App\Domain\System\Services\Impl;

use App\Domain\System\Services\SystemResponseServiceInterface;

final class SystemResponseService implements SystemResponseServiceInterface
{
    public function buildSystemInfoResponse(array $systemInfo): array
    {
        return [
            'app' => 'Projeto de Treinamento PHP-OO',
            'version' => '1.0.0',
            'system' => $systemInfo,
            'environment_info' => $this->buildEnvironmentInfo(),
        ];
    }

    public function buildEnvironmentInfo(): array
    {
        return [
            'working_directory' => getcwd(),
            'script_path' => __FILE__,
            'vendor_path' => realpath(__DIR__ . '/../../../../vendor'),
            'cache_path' => realpath(__DIR__ . '/../../../../cache'),
            'docker_environment' => getenv('DOCKER') ? 'Sim' : 'Não',
        ];
    }
}
