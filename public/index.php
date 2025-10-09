<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Impl\ApiApplication;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

// Criar aplicação Slim
$app = ApiApplication::getInstance()->createSlimApp();

// Criar ServerRequest usando Nyholm PSR-7
$psr17Factory = new Psr17Factory();
$creator = new ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);

$request = $creator->fromGlobals();

// Executar aplicação
$app->run($request);
