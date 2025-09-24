<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud;

use Psr\Http\Message\ServerRequestInterface;

interface RequestValidatorInterface
{
    public function validateCreateCommand(ServerRequestInterface $request);
    
    public function validateUpdateCommand(ServerRequestInterface $request);
}
