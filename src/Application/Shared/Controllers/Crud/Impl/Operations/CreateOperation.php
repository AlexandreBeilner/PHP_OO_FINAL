<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud\Impl\Operations;

use App\Application\Shared\Controllers\Crud\CommandExecutorInterface;
use App\Application\Shared\Controllers\Crud\CrudOperationInterface;
use App\Application\Shared\Controllers\Crud\CrudResultInterface;
use App\Application\Shared\Controllers\Crud\Impl\CrudResult;
use App\Application\Shared\Controllers\Crud\RequestValidatorInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CreateOperation implements CrudOperationInterface
{
    private CommandExecutorInterface $executor;
    private string $successMessage;
    private RequestValidatorInterface $validator;

    public function __construct(
        RequestValidatorInterface $validator,
        CommandExecutorInterface $executor,
        string $successMessage = 'Criado com sucesso'
    ) {
        $this->validator = $validator;
        $this->executor = $executor;
        $this->successMessage = $successMessage;
    }

    public function execute(ServerRequestInterface $request, array $pathParams = []): CrudResultInterface
    {
        $command = $this->validator->validateCreateCommand($request);
        $result = $this->executor->execute($command);

        return new CrudResult($result, $this->successMessage, 201);
    }
}
