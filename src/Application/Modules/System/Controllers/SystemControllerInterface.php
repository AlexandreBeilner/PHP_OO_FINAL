<?php

declare(strict_types=1);

namespace App\Application\Modules\System\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface SystemControllerInterface
{
    public function getAppInfo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;

    public function getSystemInfo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;

    public function testDatabase(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;

    public function testDoctrine(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}
