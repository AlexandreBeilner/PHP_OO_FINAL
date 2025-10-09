<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\Factories\Impl;

use App\Application\Shared\Controllers\Crud\CrudOperationFactoryInterface;
use App\Application\Shared\Controllers\Crud\CrudOperationInterface;
use App\Application\Shared\Controllers\Crud\Impl\Operations\CreateOperation;
use App\Application\Shared\Controllers\Crud\Impl\Operations\DeleteOperation;
use App\Application\Shared\Controllers\Crud\Impl\Operations\IndexOperation;
use App\Application\Shared\Controllers\Crud\Impl\Operations\ShowOperation;
use App\Application\Shared\Controllers\Crud\Impl\Operations\UpdateOperation;

final class UserCrudOperationFactory implements CrudOperationFactoryInterface
{
    private UserCommandExecutor $executor;
    private UserRequestValidator $validator;

    public function __construct(
        UserRequestValidator $validator,
        UserCommandExecutor $executor
    ) {
        $this->validator = $validator;
        $this->executor = $executor;
    }

    public function createCreateOperation(): CrudOperationInterface
    {
        return new CreateOperation(
            $this->validator,
            $this->executor,
            'Usuário criado com sucesso'
        );
    }

    public function createDeleteOperation(): CrudOperationInterface
    {
        return new DeleteOperation(
            $this->executor,
            'Usuário deletado com sucesso',
            'Usuário não encontrado'
        );
    }

    public function createIndexOperation(): CrudOperationInterface
    {
        return new IndexOperation(
            $this->executor,
            'Usuários listados com sucesso'
        );
    }

    public function createShowOperation(): CrudOperationInterface
    {
        return new ShowOperation(
            $this->executor,
            'Usuário encontrado com sucesso'
        );
    }

    public function createUpdateOperation(): CrudOperationInterface
    {
        return new UpdateOperation(
            $this->validator,
            $this->executor,
            'Usuário atualizado com sucesso',
            'Usuário não encontrado'
        );
    }
}
