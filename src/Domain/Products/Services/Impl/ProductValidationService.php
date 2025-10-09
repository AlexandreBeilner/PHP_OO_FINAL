<?php

declare(strict_types=1);

namespace App\Domain\Products\Services\Impl;

use App\Application\Shared\DTOs\Impl\ValidationResult;
use App\Domain\Common\Exceptions\Impl\ValidationException;
use App\Domain\Products\Commands\Impl\CreateProductCommand;
use App\Domain\Products\Commands\Impl\UpdateProductCommand;
use App\Domain\Products\DTOs\Impl\CreateProductDataDTO;
use App\Domain\Products\DTOs\Impl\UpdateProductDataDTO;
use App\Domain\Products\Services\ProductValidationServiceInterface;
use App\Domain\Products\Validators\ProductDataValidatorInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ProductValidationService implements ProductValidationServiceInterface
{
    private ProductDataValidatorInterface $productDataValidator;

    public function __construct(ProductDataValidatorInterface $productDataValidator)
    {
        $this->productDataValidator = $productDataValidator;
    }

    public function validateCreateProductCommand(ServerRequestInterface $request): CreateProductCommand
    {
        $data = $request->getParsedBody();

        $validation = $this->productDataValidator->validateCreateProductData($data);
        if (!$validation->isValid()) {
            throw new ValidationException('Dados para criação de produto inválidos', $validation->getErrors());
        }

        return CreateProductCommand::fromArray($data);
    }

    public function validateCreateProductRequest(ServerRequestInterface $request): CreateProductDataDTO
    {
        $data = $request->getParsedBody();

        $validation = $this->productDataValidator->validateCreateProductData($data);
        if (!$validation->isValid()) {
            throw new ValidationException('Dados para criação de produto inválidos', $validation->getErrors());
        }

        return CreateProductDataDTO::fromArray($data);
    }

    public function validateUpdateProductCommand(ServerRequestInterface $request): UpdateProductCommand
    {
        $data = $request->getParsedBody();

        $validation = $this->productDataValidator->validateUpdateProductData($data);
        if (!$validation->isValid()) {
            throw new ValidationException('Dados para atualização de produto inválidos', $validation->getErrors());
        }

        return UpdateProductCommand::fromArray($data);
    }

    public function validateUpdateProductRequest(ServerRequestInterface $request): UpdateProductDataDTO
    {
        $data = $request->getParsedBody();

        $validation = $this->productDataValidator->validateUpdateProductData($data);
        if (!$validation->isValid()) {
            throw new ValidationException('Dados para atualização de produto inválidos', $validation->getErrors());
        }

        return UpdateProductDataDTO::fromArray($data);
    }

    public function validateProductId(int $id): ValidationResult
    {
        $errors = [];

        if ($id <= 0) {
            $errors['id'] = 'ID do produto é obrigatório e deve ser um número positivo';
        }

        return new ValidationResult(empty($errors), $errors);
    }
}
