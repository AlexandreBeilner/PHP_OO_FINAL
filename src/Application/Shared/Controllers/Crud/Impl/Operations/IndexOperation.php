<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud\Impl\Operations;

use App\Application\Shared\Controllers\Crud\CommandExecutorInterface;
use App\Application\Shared\Controllers\Crud\CrudOperationInterface;
use App\Application\Shared\Controllers\Crud\CrudResultInterface;
use App\Application\Shared\Controllers\Crud\Impl\CrudResult;
use Psr\Http\Message\ServerRequestInterface;

final class IndexOperation implements CrudOperationInterface
{
    private CommandExecutorInterface $executor;
    private string $successMessage;

    public function __construct(
        CommandExecutorInterface $executor,
        string $successMessage = 'Listado com sucesso'
    ) {
        $this->executor = $executor;
        $this->successMessage = $successMessage;
    }

    public function execute(ServerRequestInterface $request, array $pathParams = []): CrudResultInterface
    {
        $result = $this->findAllResources();
        
        return new CrudResult($result, $this->successMessage, 200);
    }

    private function findAllResources()
    {
        return $this->executor->findAll();
    }
}
