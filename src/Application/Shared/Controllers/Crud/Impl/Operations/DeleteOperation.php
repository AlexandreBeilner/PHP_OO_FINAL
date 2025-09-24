<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud\Impl\Operations;

use App\Application\Shared\Controllers\Crud\CommandExecutorInterface;
use App\Application\Shared\Controllers\Crud\CrudOperationInterface;
use App\Application\Shared\Controllers\Crud\CrudResultInterface;
use App\Application\Shared\Controllers\Crud\Impl\CrudResult;
use App\Domain\Common\Exceptions\Impl\BusinessLogicExceptionAbstract;
use Psr\Http\Message\ServerRequestInterface;

final class DeleteOperation implements CrudOperationInterface
{
    private CommandExecutorInterface $executor;
    private string $successMessage;
    private string $notFoundMessage;

    public function __construct(
        CommandExecutorInterface $executor,
        string $successMessage = 'Deletado com sucesso',
        string $notFoundMessage = 'Recurso não encontrado'
    ) {
        $this->executor = $executor;
        $this->successMessage = $successMessage;
        $this->notFoundMessage = $notFoundMessage;
    }

    public function execute(ServerRequestInterface $request, array $pathParams = []): CrudResultInterface
    {
        $id = $this->extractIdFrom($request, $pathParams);
        $this->performDeletion($id);
        
        return new CrudResult(null, $this->successMessage, 200);
    }

    private function extractIdFrom(ServerRequestInterface $request, array $pathParams): int
    {
        $id = (int) ($pathParams['id'] ?? $request->getAttribute('id'));
        
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID é obrigatório para operação de exclusão');
        }
        
        return $id;
    }

    private function performDeletion(int $id): void
    {
        $deleted = $this->executor->deleteById($id);
        
        if (!$deleted) {
            throw $this->createNotFoundException();
        }
    }

    private function createNotFoundException(): BusinessLogicExceptionAbstract
    {
        return new class($this->notFoundMessage) extends BusinessLogicExceptionAbstract {
            public function __construct(string $message)
            {
                parent::__construct($message, 404);
            }
        };
    }
}
