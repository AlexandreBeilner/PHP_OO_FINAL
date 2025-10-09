<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Modules\Security\EntityPaths;

use PHPUnit\Framework\TestCase;
use App\Application\Modules\Security\EntityPaths\Impl\SecurityEntityPathProvider;
use App\Application\Shared\EntityPaths\EntityPathProviderInterface;

final class SecurityEntityPathProviderTest extends TestCase
{
    private SecurityEntityPathProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new SecurityEntityPathProvider();
    }

    public function testImplementsEntityPathProviderInterface(): void
    {
        $this->assertInstanceOf(EntityPathProviderInterface::class, $this->provider);
    }

    public function testConstructorInitializesCorrectly(): void
    {
        $provider = new SecurityEntityPathProvider();

        $this->assertInstanceOf(SecurityEntityPathProvider::class, $provider);
    }

    public function testHasEntityPathsReturnsBoolean(): void
    {
        $result = $this->provider->hasEntityPaths();

        $this->assertIsBool($result);
    }

    public function testGetEntityPathsReturnsArray(): void
    {
        $paths = $this->provider->getEntityPaths();

        $this->assertIsArray($paths);
    }

    public function testGetEntityPathsReturnsExpectedStructure(): void
    {
        $paths = $this->provider->getEntityPaths();

        // Should return array of strings (paths)
        foreach ($paths as $path) {
            $this->assertIsString($path, "Each path should be a string");
        }
    }

    public function testGetEntityPathsContainsSecurityEntitiesPath(): void
    {
        $paths = $this->provider->getEntityPaths();

        if (!empty($paths)) {
            $firstPath = $paths[0];
            $this->assertStringContainsString('Security/Entities', $firstPath);
            $this->assertStringContainsString('src/Domain', $firstPath);
        }
    }

    public function testHasEntityPathsConsistentWithGetEntityPaths(): void
    {
        $hasEntityPaths = $this->provider->hasEntityPaths();
        $entityPaths = $this->provider->getEntityPaths();

        if ($hasEntityPaths) {
            $this->assertNotEmpty($entityPaths, "If hasEntityPaths is true, getEntityPaths should not be empty");
        } else {
            $this->assertEmpty($entityPaths, "If hasEntityPaths is false, getEntityPaths should be empty");
        }
    }

    public function testGetEntityPathsWhenPathExists(): void
    {
        // This test assumes the directory structure exists
        $paths = $this->provider->getEntityPaths();

        if ($this->provider->hasEntityPaths()) {
            $this->assertCount(1, $paths, "Should return exactly one path for Security entities");
            $this->assertStringEndsWith('/src/Domain/Security/Entities/Impl', $paths[0]);
        }
    }

    public function testGetEntityPathsReturnsAbsolutePaths(): void
    {
        $paths = $this->provider->getEntityPaths();

        foreach ($paths as $path) {
            // On Unix-like systems, absolute paths start with '/'
            // On Windows, they start with a drive letter like 'C:'
            $this->assertTrue(
                str_starts_with($path, '/') || preg_match('/^[A-Z]:[\/\\\\]/', $path),
                "Path should be absolute: {$path}"
            );
        }
    }

    public function testMultipleCallsReturnSameResults(): void
    {
        $firstCall = $this->provider->getEntityPaths();
        $secondCall = $this->provider->getEntityPaths();

        $this->assertEquals($firstCall, $secondCall, "Multiple calls should return identical results");

        $firstHas = $this->provider->hasEntityPaths();
        $secondHas = $this->provider->hasEntityPaths();

        $this->assertEquals($firstHas, $secondHas, "hasEntityPaths should return same result on multiple calls");
    }

    public function testProviderIsStateless(): void
    {
        $provider1 = new SecurityEntityPathProvider();
        $provider2 = new SecurityEntityPathProvider();

        $paths1 = $provider1->getEntityPaths();
        $paths2 = $provider2->getEntityPaths();

        $this->assertEquals($paths1, $paths2, "Different instances should return same paths");

        $has1 = $provider1->hasEntityPaths();
        $has2 = $provider2->hasEntityPaths();

        $this->assertEquals($has1, $has2, "Different instances should have same hasEntityPaths result");
    }
}
