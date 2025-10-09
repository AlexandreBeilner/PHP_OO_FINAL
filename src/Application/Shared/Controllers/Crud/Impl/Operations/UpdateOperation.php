<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud\Impl\Operations;

use App\Application\Shared\Controllers\Crud\CommandExecutorInterface;
use App\Application\Shared\Controllers\Crud\CrudOperationInterface;
use App\Application\Shared\Controllers\Crud\CrudResultInterface;
use App\Application\Shared\Controllers\Crud\Impl\CrudResult;
use App\Application\Shared\Controllers\Crud\RequestValidatorInterface;
use App\Domain\Common\Exceptions\Impl\BusinessLogicExceptionAbstract;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

final class UpdateOperation implements CrudOperationInterface
{
    private CommandExecutorInterface $executor;
    private string $notFoundMessage;
    private string $successMessage;
    private RequestValidatorInterface $validator;

    public function __construct(
        RequestValidatorInterface $validator,
        CommandExecutorInterface $executor,
        string $successMessage = 'Atualizado com sucesso',
        string $notFoundMessage = 'Recurso não encontrado'
    ) {
        $this->validator = $validator;
        $this->executor = $executor;
        $this->successMessage = $successMessage;
        $this->notFoundMessage = $notFoundMessage;
    }

    public function execute(ServerRequestInterface $request, array $pathParams = []): CrudResultInterface
    {
        $id = $this->extractIdFrom($request, $pathParams);
        $command = $this->validator->validateUpdateCommand($request);
        $result = $this->executeUpdateCommand($command, $id);

        return new CrudResult($result, $this->successMessage, 200);
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

    private function executeUpdateCommand($command, int $id)
    {
        $result = $this->executor->executeWithId($command, $id);

        if (! $result) {
            throw $this->createNotFoundException();
        }

        return $result;
    }

    private function extractIdFrom(ServerRequestInterface $request, array $pathParams): int
    {
        $id = (int) ($pathParams['id'] ?? $request->getAttribute('id'));

        if ($id <= 0) {
            throw new InvalidArgumentException('ID é obrigatório para operação de atualização');
        }

        return $id;
    }
}
