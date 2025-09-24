<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud\Impl\Operations;

use App\Application\Shared\Controllers\Crud\CommandExecutorInterface;
use App\Application\Shared\Controllers\Crud\CrudOperationInterface;
use App\Application\Shared\Controllers\Crud\CrudResultInterface;
use App\Application\Shared\Controllers\Crud\Impl\CrudResult;
use Psr\Http\Message\ServerRequestInterface;

final class ShowOperation implements CrudOperationInterface
{
    private CommandExecutorInterface $executor;
    private string $successMessage;

    public function __construct(
        CommandExecutorInterface $executor,
        string $successMessage = 'Encontrado com sucesso'
    ) {
        $this->executor = $executor;
        $this->successMessage = $successMessage;
    }

    public function execute(ServerRequestInterface $request, array $pathParams = []): CrudResultInterface
    {
        $id = $this->extractIdFrom($request, $pathParams);
        $result = $this->findResourceById($id);
        
        return new CrudResult($result, $this->successMessage, 200);
    }

    private function extractIdFrom(ServerRequestInterface $request, array $pathParams): int
    {
        $id = (int) ($pathParams['id'] ?? $request->getAttribute('id'));
        
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID é obrigatório para operação de busca');
        }
        
        return $id;
    }

    private function findResourceById(int $id)
    {
        return $this->executor->findById($id);
    }
}
