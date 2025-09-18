<?php

declare(strict_types=1);

namespace App\Application\Modules\Security\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface UserControllerInterface
{
    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;

    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface;

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;

    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface;

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface;
}
