<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud;

use Psr\Http\Message\ServerRequestInterface;

interface CrudOperationInterface
{
    /**
     * Executa a operação CRUD específica
     * 
     * @param ServerRequestInterface $request Request HTTP completo
     * @param array $pathParams Parâmetros da URL (ex: ['id' => '123'])
     * @return CrudResultInterface Resultado da operação
     * @throws \App\Domain\Common\Exceptions\Impl\ValidationException Em caso de dados inválidos
     * @throws \App\Domain\Common\Exceptions\Impl\BusinessLogicExceptionAbstract Em caso de regra de negócio
     * @throws \Exception Em caso de erro geral
     */
    public function execute(ServerRequestInterface $request, array $pathParams = []): CrudResultInterface;
}
