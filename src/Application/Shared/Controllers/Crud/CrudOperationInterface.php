<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud;

use App\Domain\Common\Exceptions\Impl\BusinessLogicExceptionAbstract;
use App\Domain\Common\Exceptions\Impl\ValidationException;
use Exception;
use Psr\Http\Message\ServerRequestInterface;

interface CrudOperationInterface
{
    /**
     * Executa a operação CRUD específica
     *
     * @param ServerRequestInterface $request Request HTTP completo
     * @param array $pathParams Parâmetros da URL (ex: ['id' => '123'])
     * @return CrudResultInterface Resultado da operação
     * @throws ValidationException Em caso de dados inválidos
     * @throws BusinessLogicExceptionAbstract Em caso de regra de negócio
     * @throws Exception Em caso de erro geral
     */
    public function execute(ServerRequestInterface $request, array $pathParams = []): CrudResultInterface;
}
