<?php

declare(strict_types=1);

namespace Tests\Integration\API;

use Tests\Integration\AbstractBaseHttpIntegrationTest;

final class SimpleApiTest extends AbstractBaseHttpIntegrationTest
{
    public function testApiIsWorking(): void
    {
        $request = $this->createRequest('GET', '/app-status');
        $response = $this->executeRequest($request);

        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('body', $response);
    }

    public function testApiHasRoutes(): void
    {
        $routes = $this->slimApp->getRouteCollector()->getRoutes();
        $this->assertGreaterThan(0, count($routes));
    }

    public function testApiHasUserRoutes(): void
    {
        $routes = $this->slimApp->getRouteCollector()->getRoutes();
        $routePatterns = [];
        
        foreach ($routes as $route) {
            $routePatterns[] = $route->getPattern();
        }
        
        $this->assertContains('/api/security/users', $routePatterns);
        $this->assertContains('/api/auth/login', $routePatterns);
    }
}
