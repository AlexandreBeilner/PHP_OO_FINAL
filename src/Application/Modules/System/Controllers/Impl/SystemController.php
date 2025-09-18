<?php

declare(strict_types=1);

namespace App\Application\Modules\System\Controllers\Impl;

use App\Application\Common\Controllers\Impl\AbstractBaseController;
use App\Application\Modules\System\Controllers\SystemControllerInterface;
use App\Domain\System\Services\SystemServiceInterface;
use App\Domain\System\Services\SystemResponseServiceInterface;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SystemController extends AbstractBaseController implements SystemControllerInterface
{
    private SystemServiceInterface $systemService;
    private SystemResponseServiceInterface $systemResponseService;

    public function __construct(
        SystemServiceInterface $systemService,
        SystemResponseServiceInterface $systemResponseService
    ) {
        $this->systemService = $systemService;
        $this->systemResponseService = $systemResponseService;
    }

    public function getAppInfo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Alias para getSystemInfo para compatibilidade
        return $this->getSystemInfo($request, $response);
    }

    public function getRequiredExtensionsStatus(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $extensionsStatus = $this->systemService->getRequiredExtensionsStatus();

            $apiResponse = $this->success($extensionsStatus, 'Status das extensões obtido com sucesso');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (Exception $e) {
            $apiResponse = $this->error('Erro ao obter status das extensões: ' . $e->getMessage(), 500);
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }

    public function getSystemInfo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $systemInfo = $this->systemService->getSystemInfo();
            $data = $this->systemResponseService->buildSystemInfoResponse($systemInfo);

            $apiResponse = $this->success($data, 'Informações do sistema recuperadas com sucesso');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (Exception $e) {
            $apiResponse = $this->error('Erro ao obter informações do sistema: ' . $e->getMessage(), 500);
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }

    public function testDatabase(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Alias para testDoctrine para compatibilidade
        return $this->testDoctrine($request, $response);
    }

    public function testDoctrine(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $testResult = $this->systemService->testDatabase();

            $apiResponse = $this->success($testResult, 'Teste do Doctrine executado com sucesso');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        } catch (Exception $e) {
            $apiResponse = $this->error('Erro ao executar teste do Doctrine: ' . $e->getMessage(), 500);
            $response->getBody()->write($apiResponse->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus($apiResponse->getCode());
        }
    }
}