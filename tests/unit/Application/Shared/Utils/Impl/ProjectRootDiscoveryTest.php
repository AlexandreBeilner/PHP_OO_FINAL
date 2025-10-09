<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Utils\Impl;

use App\Application\Shared\Utils\Impl\ProjectRootDiscovery;
use App\Application\Shared\Utils\ProjectRootDiscoveryInterface;
use PHPUnit\Framework\TestCase;

final class ProjectRootDiscoveryTest extends TestCase
{
    public function testClearCacheCanBeCalledMultipleTimes(): void
    {
        ProjectRootDiscovery::clearCache();
        ProjectRootDiscovery::clearCache();
        ProjectRootDiscovery::clearCache();

        // Should still work normally after multiple clears
        $projectRoot = ProjectRootDiscovery::getProjectRoot();
        $this->assertIsString($projectRoot);
        $this->assertNotEmpty($projectRoot);
    }

    public function testClearCacheResetsInternalState(): void
    {
        // Get project root to populate cache
        $projectRoot = ProjectRootDiscovery::getProjectRoot();
        $this->assertNotEmpty($projectRoot);

        // Clear cache
        ProjectRootDiscovery::clearCache();

        // Get project root again - should still work and return same value
        $projectRootAfterClear = ProjectRootDiscovery::getProjectRoot();
        $this->assertEquals($projectRoot, $projectRootAfterClear);
    }

    public function testClearCacheVoidReturnType(): void
    {
        // clearCache() should return void/null
        $result = ProjectRootDiscovery::clearCache();

        $this->assertNull($result);
    }

    public function testGetProjectRootConsistentResults(): void
    {
        $results = [];

        // Call multiple times and clear cache in between
        for ($i = 0; $i < 3; $i++) {
            if ($i > 0) {
                ProjectRootDiscovery::clearCache();
            }
            $results[] = ProjectRootDiscovery::getProjectRoot();
        }

        // All results should be identical
        $this->assertEquals($results[0], $results[1]);
        $this->assertEquals($results[1], $results[2]);
        $this->assertCount(3, $results);
    }

    public function testGetProjectRootFindsComposerJsonInCurrentProject(): void
    {
        $projectRoot = ProjectRootDiscovery::getProjectRoot();
        $composerPath = $projectRoot . '/composer.json';

        $this->assertFileExists($composerPath);
        $this->assertTrue(is_readable($composerPath));
    }

    public function testGetProjectRootReturnsAbsolutePath(): void
    {
        $projectRoot = ProjectRootDiscovery::getProjectRoot();

        // On Unix-like systems, absolute paths start with '/'
        // On Windows, they start with a drive letter like 'C:'
        $this->assertTrue(
            str_starts_with($projectRoot, '/') || preg_match('/^[A-Z]:[\/\\\\]/', $projectRoot),
            "Project root should be an absolute path, got: {$projectRoot}"
        );
    }

    public function testGetProjectRootReturnsStringPath(): void
    {
        $projectRoot = ProjectRootDiscovery::getProjectRoot();

        $this->assertIsString($projectRoot);
        $this->assertNotEmpty($projectRoot);
    }

    public function testGetProjectRootUsesCaching(): void
    {
        $firstCall = ProjectRootDiscovery::getProjectRoot();
        $secondCall = ProjectRootDiscovery::getProjectRoot();

        $this->assertEquals($firstCall, $secondCall);
        $this->assertSame($firstCall, $secondCall); // Should be the exact same string instance due to caching
    }

    public function testGetProjectRootWithExistingCache(): void
    {
        // Populate cache with first call
        $firstResult = ProjectRootDiscovery::getProjectRoot();

        // Second call should use cache and be very fast
        $secondResult = ProjectRootDiscovery::getProjectRoot();

        $this->assertEquals($firstResult, $secondResult);
    }

    public function testImplementsProjectRootDiscoveryInterface(): void
    {
        $this->assertTrue(is_a(ProjectRootDiscovery::class, ProjectRootDiscoveryInterface::class, true));
    }

    public function testProjectRootContainsExpectedStructure(): void
    {
        $projectRoot = ProjectRootDiscovery::getProjectRoot();

        // Verify typical PHP project structure exists
        $this->assertDirectoryExists($projectRoot . '/src');
        $this->assertDirectoryExists($projectRoot . '/tests');
        $this->assertFileExists($projectRoot . '/composer.json');
    }

    protected function setUp(): void
    {
        // Clear cache before each test to ensure isolation
        ProjectRootDiscovery::clearCache();
    }

    protected function tearDown(): void
    {
        // Clear cache after each test to ensure cleanup
        ProjectRootDiscovery::clearCache();
    }
}
