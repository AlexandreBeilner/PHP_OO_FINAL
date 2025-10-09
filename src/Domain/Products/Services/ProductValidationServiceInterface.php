<?php

declare(strict_types=1);

namespace App\Domain\Products\Services;

use App\Application\Shared\DTOs\Impl\ValidationResult;
use App\Domain\Products\Commands\Impl\CreateProductCommand;
use App\Domain\Products\Commands\Impl\UpdateProductCommand;
use App\Domain\Products\DTOs\Impl\CreateProductDataDTO;
use App\Domain\Products\DTOs\Impl\UpdateProductDataDTO;
use Psr\Http\Message\ServerRequestInterface;

interface ProductValidationServiceInterface
{
    public function validateCreateProductCommand(ServerRequestInterface $request): CreateProductCommand;

    public function validateCreateProductRequest(ServerRequestInterface $request): CreateProductDataDTO;

    public function validateUpdateProductCommand(ServerRequestInterface $request): UpdateProductCommand;

    public function validateUpdateProductRequest(ServerRequestInterface $request): UpdateProductDataDTO;

    public function validateProductId(int $id): ValidationResult;
}
