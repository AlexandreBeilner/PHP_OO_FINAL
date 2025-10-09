<?php

declare(strict_types=1);

namespace App\Application\Modules\Products\Controllers\Impl;

use App\Application\Modules\Products\Controllers\ProductControllerInterface;
use App\Application\Shared\Controllers\Impl\AbstractBaseController;
use App\Domain\Common\Exceptions\Impl\BusinessLogicExceptionAbstract;
use App\Domain\Common\Exceptions\Impl\ValidationException;
use App\Domain\Products\Services\ProductServiceInterface;
use App\Domain\Products\Services\ProductValidationServiceInterface;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ProductController extends AbstractBaseController implements ProductControllerInterface
{
    private ProductServiceInterface $productService;
    private ProductValidationServiceInterface $productValidationService;

    public function __construct(
        ProductServiceInterface $productService,
        ProductValidationServiceInterface $productValidationService
    ) {
        $this->productService = $productService;
        $this->productValidationService = $productValidationService;
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $createProductCommand = $this->productValidationService->validateCreateProductCommand($request);
            $product = $createProductCommand->executeWith($this->productService);

            $apiResponse = $this->success($product, 'Produto criado com sucesso', 201);
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (ValidationException $e) {
            $apiResponse = $this->validationError($e->getErrors(), $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (BusinessLogicExceptionAbstract $e) {
            $apiResponse = $this->error($e->getMessage(), 409);
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (Exception $e) {
            $apiResponse = $this->serverError('Erro ao criar produto: ' . $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $request->getAttribute('id'));
            $deleted = $this->productService->deleteProduct($id);

            if (!$deleted) {
                $apiResponse = $this->notFound('Produto não encontrado');
                $response->getBody()->write($apiResponse->toJson());
                return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
            }

            $apiResponse = $this->success(null, 'Produto deletado com sucesso');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (Exception $e) {
            $apiResponse = $this->serverError('Erro ao deletar produto: ' . $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $products = $this->productService->processAllProducts(fn ($product) => $product);
            $apiResponse = $this->success($products, 'Produtos listados com sucesso');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (Exception $e) {
            $apiResponse = $this->serverError('Erro ao listar produtos: ' . $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $request->getAttribute('id'));
            $product = $this->productService->processProductById($id, fn ($product) => $product);

            $apiResponse = $this->success($product, 'Produto encontrado com sucesso');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (BusinessLogicExceptionAbstract $e) {
            $apiResponse = $this->notFound('Produto não encontrado');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (Exception $e) {
            $apiResponse = $this->serverError('Erro ao buscar produto: ' . $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $request->getAttribute('id'));
            $updateProductCommand = $this->productValidationService->validateUpdateProductCommand($request);
            $product = $updateProductCommand->executeWithProductId($this->productService, $id);

            if (!$product) {
                $apiResponse = $this->notFound('Produto não encontrado');
                $response->getBody()->write($apiResponse->toJson());
                return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
            }

            $apiResponse = $this->success($product, 'Produto atualizado com sucesso');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (ValidationException $e) {
            $apiResponse = $this->validationError($e->getErrors(), $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (BusinessLogicExceptionAbstract $e) {
            $apiResponse = $this->error($e->getMessage(), 409);
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (Exception $e) {
            $apiResponse = $this->serverError('Erro ao atualizar produto: ' . $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }
}
