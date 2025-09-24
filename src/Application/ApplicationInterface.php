<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\Shared\Http\SlimAppFactoryInterface;
use DI\Container;
use Slim\App;

interface ApplicationInterface
{
    public function container(): Container;

    public function createSlimApp(): App;

    public static function getInstance(): self;

    public function getSlimAppFactory(): SlimAppFactoryInterface;
}
