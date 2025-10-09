<?php

declare(strict_types=1);

namespace App\Domain\Common\Exceptions\Impl;

use App\Domain\Common\Exceptions\BusinessLogicExceptionInterface;

final class BusinessLogicExceptionAbstract extends AbstractBaseException implements BusinessLogicExceptionInterface
{
    public function __construct(string $message = "Business logic error", int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
