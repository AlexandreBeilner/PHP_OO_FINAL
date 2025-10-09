<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Registry\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Registry\Impl\BootstrapRegistry;
use App\Application\Shared\Registry\BootstrapRegistryInterface;
use App\Application\Shared\BootstrapInterface;

final class BootstrapRegistryTest extends TestCase
{
    private BootstrapRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new BootstrapRegistry();
    }

    public function testImplementsBootstrapRegistryInterface(): void
    {
        $this->assertInstanceOf(BootstrapRegistryInterface::class, $this->registry);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $registry = new BootstrapRegistry();

        $this->assertInstanceOf(BootstrapRegistry::class, $registry);
        $this->assertInstanceOf(BootstrapRegistryInterface::class, $registry);
    }

    public function testRegisterVoidReturnType(): void
    {
        $bootstrap = $this->createMock(BootstrapInterface::class);

        $result = $this->registry->register($bootstrap);

        $this->assertNull($result);
    }

    public function testRegisterAddsBootstrapToCollection(): void
    {
        $bootstrap = $this->createMock(BootstrapInterface::class);

        $this->registry->register($bootstrap);

        $all = $this->registry->getAll();
        $this->assertCount(1, $all);
        $this->assertSame($bootstrap, $all[0]);
    }

    public function testRegisterMultipleBootstraps(): void
    {
        $bootstrap1 = $this->createMock(BootstrapInterface::class);
        $bootstrap2 = $this->createMock(BootstrapInterface::class);
        $bootstrap3 = $this->createMock(BootstrapInterface::class);

        $this->registry->register($bootstrap1);
        $this->registry->register($bootstrap2);
        $this->registry->register($bootstrap3);

        $all = $this->registry->getAll();
        $this->assertCount(3, $all);
        $this->assertSame($bootstrap1, $all[0]);
        $this->assertSame($bootstrap2, $all[1]);
        $this->assertSame($bootstrap3, $all[2]);
    }

    public function testGetAllReturnsEmptyArrayInitially(): void
    {
        $all = $this->registry->getAll();

        $this->assertIsArray($all);
        $this->assertEmpty($all);
    }

    public function testGetAllReturnsAllRegisteredBootstraps(): void
    {
        $bootstrap1 = $this->createMock(BootstrapInterface::class);
        $bootstrap2 = $this->createMock(BootstrapInterface::class);

        $this->registry->register($bootstrap1);
        $this->registry->register($bootstrap2);

        $all = $this->registry->getAll();
        $this->assertCount(2, $all);
        $this->assertContains($bootstrap1, $all);
        $this->assertContains($bootstrap2, $all);
    }

    public function testFindByModuleReturnsNullWhenEmpty(): void
    {
        $result = $this->registry->findByModule('testModule');

        $this->assertNull($result);
    }

    public function testFindByModuleReturnsNullWhenNotFound(): void
    {
        $bootstrap = $this->createMock(BootstrapInterface::class);
        $bootstrap->expects($this->once())
            ->method('belongsToModule')
            ->with('testModule')
            ->willReturn(false);

        $this->registry->register($bootstrap);

        $result = $this->registry->findByModule('testModule');
        $this->assertNull($result);
    }

    public function testFindByModuleReturnsMatchingBootstrap(): void
    {
        $bootstrap1 = $this->createMock(BootstrapInterface::class);
        $bootstrap1->expects($this->once())
            ->method('belongsToModule')
            ->with('testModule')
            ->willReturn(false);

        $bootstrap2 = $this->createMock(BootstrapInterface::class);
        $bootstrap2->expects($this->once())
            ->method('belongsToModule')
            ->with('testModule')
            ->willReturn(true);

        $this->registry->register($bootstrap1);
        $this->registry->register($bootstrap2);

        $result = $this->registry->findByModule('testModule');
        $this->assertSame($bootstrap2, $result);
    }

    public function testFindByModuleReturnsFirstMatchingBootstrap(): void
    {
        $bootstrap1 = $this->createMock(BootstrapInterface::class);
        $bootstrap1->expects($this->once())
            ->method('belongsToModule')
            ->with('testModule')
            ->willReturn(true);

        $bootstrap2 = $this->createMock(BootstrapInterface::class);
        $bootstrap2->expects($this->never())
            ->method('belongsToModule');

        $this->registry->register($bootstrap1);
        $this->registry->register($bootstrap2);

        $result = $this->registry->findByModule('testModule');
        $this->assertSame($bootstrap1, $result);
    }

    public function testGetAllSortedByPriorityWithEmptyRegistry(): void
    {
        $sorted = $this->registry->getAllSortedByPriority();

        $this->assertIsArray($sorted);
        $this->assertEmpty($sorted);
    }

    public function testGetAllSortedByPriorityWithSingleBootstrap(): void
    {
        $bootstrap = $this->createMock(BootstrapInterface::class);
        $this->registry->register($bootstrap);

        $sorted = $this->registry->getAllSortedByPriority();

        $this->assertCount(1, $sorted);
        $this->assertSame($bootstrap, $sorted[0]);
    }

    public function testGetAllSortedByPriorityOrdersCorrectly(): void
    {
        $highPriorityBootstrap = $this->createMock(BootstrapInterface::class);
        $lowPriorityBootstrap = $this->createMock(BootstrapInterface::class);

        // High priority bootstrap should come first
        $highPriorityBootstrap->expects($this->once())
            ->method('hasPriorityOver')
            ->with($lowPriorityBootstrap)
            ->willReturn(true);

        $lowPriorityBootstrap->expects($this->once())
            ->method('hasPriorityOver')
            ->with($highPriorityBootstrap)
            ->willReturn(false);

        // Register in reverse order to test sorting
        $this->registry->register($lowPriorityBootstrap);
        $this->registry->register($highPriorityBootstrap);

        $sorted = $this->registry->getAllSortedByPriority();

        $this->assertCount(2, $sorted);
        $this->assertSame($highPriorityBootstrap, $sorted[0]);
        $this->assertSame($lowPriorityBootstrap, $sorted[1]);
    }

    public function testGetAllSortedByPriorityWithEqualPriorities(): void
    {
        $bootstrap1 = $this->createMock(BootstrapInterface::class);
        $bootstrap2 = $this->createMock(BootstrapInterface::class);

        $bootstrap1->expects($this->once())
            ->method('hasPriorityOver')
            ->with($bootstrap2)
            ->willReturn(false);

        $bootstrap2->expects($this->once())
            ->method('hasPriorityOver')
            ->with($bootstrap1)
            ->willReturn(false);

        $this->registry->register($bootstrap1);
        $this->registry->register($bootstrap2);

        $sorted = $this->registry->getAllSortedByPriority();

        $this->assertCount(2, $sorted);
        // Order should remain the same as registration when priorities are equal
        $this->assertSame($bootstrap1, $sorted[0]);
        $this->assertSame($bootstrap2, $sorted[1]);
    }

    public function testGetAllSortedByPriorityDoesNotModifyOriginalArray(): void
    {
        $bootstrap1 = $this->createMock(BootstrapInterface::class);
        $bootstrap2 = $this->createMock(BootstrapInterface::class);

        $bootstrap2->method('hasPriorityOver')->willReturn(true);
        $bootstrap1->method('hasPriorityOver')->willReturn(false);

        $this->registry->register($bootstrap1);
        $this->registry->register($bootstrap2);

        $originalOrder = $this->registry->getAll();
        $sortedOrder = $this->registry->getAllSortedByPriority();

        // Original array should remain unchanged
        $this->assertSame($bootstrap1, $originalOrder[0]);
        $this->assertSame($bootstrap2, $originalOrder[1]);

        // Sorted array should be in priority order
        $this->assertSame($bootstrap2, $sortedOrder[0]);
        $this->assertSame($bootstrap1, $sortedOrder[1]);
    }

    public function testRegistryIsStateless(): void
    {
        $registry1 = new BootstrapRegistry();
        $registry2 = new BootstrapRegistry();

        $bootstrap = $this->createMock(BootstrapInterface::class);

        $registry1->register($bootstrap);

        // Different instances should be independent
        $this->assertCount(1, $registry1->getAll());
        $this->assertCount(0, $registry2->getAll());
    }

    public function testCanRegisterSameBootstrapMultipleTimes(): void
    {
        $bootstrap = $this->createMock(BootstrapInterface::class);

        $this->registry->register($bootstrap);
        $this->registry->register($bootstrap);

        // Should allow duplicate registrations
        $all = $this->registry->getAll();
        $this->assertCount(2, $all);
        $this->assertSame($bootstrap, $all[0]);
        $this->assertSame($bootstrap, $all[1]);
    }
}
