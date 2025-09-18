<?php

declare(strict_types=1);

namespace Tests\Unit\Common\Services\Impl;

use PHPUnit\Framework\TestCase;
use App\Domain\Common\Services\Impl\AbstractService;
use App\Domain\Common\Repositories\AbstractRepositoryInterface;
use App\Domain\Common\Validators\ValidatorInterface;

final class AbstractServiceTest extends TestCase
{
    private AbstractService $abstractService;
    private AbstractRepositoryInterface $mockRepository;
    private ValidatorInterface $mockValidator;

    protected function setUp(): void
    {
        $this->mockRepository = $this->createMock(AbstractRepositoryInterface::class);
        $this->mockValidator = $this->createMock(ValidatorInterface::class);
        
        $this->abstractService = new class($this->mockRepository, $this->mockValidator) extends AbstractService {
            protected function extractEntityData(object $entity): array
            {
                return ['test' => 'data'];
            }
        };
    }


    public function testCount(): void
    {
        $criteria = ['status' => 'active'];
        $expectedCount = 5;
        
        $this->mockRepository->expects($this->once())
            ->method('count')
            ->with($criteria)
            ->willReturn($expectedCount);

        $result = $this->abstractService->count($criteria);
        $this->assertEquals($expectedCount, $result);
    }

    public function testExists(): void
    {
        $this->mockRepository->expects($this->once())
            ->method('count')
            ->with(['id' => 1])
            ->willReturn(1);

        $result = $this->abstractService->exists(1);
        $this->assertTrue($result);
    }

    public function testExistsReturnsFalseWhenEntityNotFound(): void
    {
        $this->mockRepository->expects($this->once())
            ->method('count')
            ->with(['id' => 999])
            ->willReturn(0);

        $result = $this->abstractService->exists(999);
        $this->assertFalse($result);
    }
}
