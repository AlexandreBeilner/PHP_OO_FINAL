<?php

declare(strict_types=1);

namespace App\Application\Modules\Auth\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface AuthControllerInterface
{
    public function activateUser(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface;

    public function changePassword(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;

    public function deactivateUser(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface;

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}
