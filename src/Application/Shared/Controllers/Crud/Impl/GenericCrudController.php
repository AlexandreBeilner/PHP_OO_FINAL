<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud\Impl;

use App\Application\Shared\Controllers\Impl\AbstractBaseController;
use App\Application\Shared\Controllers\Crud\CrudOperationFactoryInterface;
use App\Application\Shared\Controllers\Crud\HttpRequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GenericCrudController extends AbstractBaseController
{
    private HttpRequestHandlerInterface $requestHandler;
    private CrudOperationFactoryInterface $operationFactory;

    public function __construct(
        HttpRequestHandlerInterface $requestHandler,
        CrudOperationFactoryInterface $operationFactory
    ) {
        $this->requestHandler = $requestHandler;
        $this->operationFactory = $operationFactory;
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $operation = $this->operationFactory->createCreateOperation();
        return $this->requestHandler->handle($request, $response, $operation);
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $operation = $this->operationFactory->createUpdateOperation();
        return $this->requestHandler->handle($request, $response, $operation, $args);
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $operation = $this->operationFactory->createDeleteOperation();
        return $this->requestHandler->handle($request, $response, $operation, $args);
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $operation = $this->operationFactory->createShowOperation();
        return $this->requestHandler->handle($request, $response, $operation, $args);
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $operation = $this->operationFactory->createIndexOperation();
        return $this->requestHandler->handle($request, $response, $operation);
    }
}
