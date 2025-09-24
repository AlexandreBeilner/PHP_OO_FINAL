<?php

declare(strict_types=1);

namespace App\Application\Shared\Http;

use DI\Container;
use Slim\App;

interface SlimAppFactoryInterface
{
    public function create(): App;

    public function getContainer(): Container;
}
