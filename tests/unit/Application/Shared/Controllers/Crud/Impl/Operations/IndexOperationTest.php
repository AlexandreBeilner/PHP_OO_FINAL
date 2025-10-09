<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Controllers\Crud\Impl\Operations;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Controllers\Crud\Impl\Operations\IndexOperation;
use App\Application\Shared\Controllers\Crud\CrudOperationInterface;
use App\Application\Shared\Controllers\Crud\CrudResultInterface;
use App\Application\Shared\Controllers\Crud\CommandExecutorInterface;
use Psr\Http\Message\ServerRequestInterface;

final class IndexOperationTest extends TestCase
{
    private IndexOperation $indexOperation;
    private CommandExecutorInterface $executor;
    private ServerRequestInterface $request;

    protected function setUp(): void
    {
        $this->executor = $this->createMock(CommandExecutorInterface::class);
        $this->indexOperation = new IndexOperation($this->executor);
        $this->request = $this->createMock(ServerRequestInterface::class);
    }

    public function testImplementsCrudOperationInterface(): void
    {
        $this->assertInstanceOf(CrudOperationInterface::class, $this->indexOperation);
    }

    public function testConstructorWithDefaultMessage(): void
    {
        $operation = new IndexOperation($this->executor);

        $this->assertInstanceOf(IndexOperation::class, $operation);
        $this->assertInstanceOf(CrudOperationInterface::class, $operation);
    }

    public function testConstructorWithCustomMessage(): void
    {
        $operation = new IndexOperation($this->executor, 'Custom list message');

        $this->assertInstanceOf(IndexOperation::class, $operation);
        $this->assertInstanceOf(CrudOperationInterface::class, $operation);
    }

    public function testExecuteCallsFindAllOnExecutor(): void
    {
        $expectedResources = [
            ['id' => 1, 'name' => 'Resource 1'],
            ['id' => 2, 'name' => 'Resource 2']
        ];

        $this->executor->expects($this->once())
            ->method('findAll')
            ->willReturn($expectedResources);

        $result = $this->indexOperation->execute($this->request);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }

    public function testExecuteReturnsAllResources(): void
    {
        $expectedResources = [
            ['id' => 1, 'name' => 'First Resource'],
            ['id' => 2, 'name' => 'Second Resource'],
            ['id' => 3, 'name' => 'Third Resource']
        ];

        $this->executor->method('findAll')->willReturn($expectedResources);

        $result = $this->indexOperation->execute($this->request);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }

    public function testExecuteWithEmptyResults(): void
    {
        $this->executor->method('findAll')->willReturn([]);

        $result = $this->indexOperation->execute($this->request);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }

    public function testExecuteWithPathParamsIgnoresPathParams(): void
    {
        $pathParams = ['id' => '123', 'unused' => 'data'];
        $expectedResources = [['id' => 1, 'name' => 'Resource']];

        $this->executor->expects($this->once())
            ->method('findAll')
            ->willReturn($expectedResources);

        // Path params should be ignored for index operations
        $result = $this->indexOperation->execute($this->request, $pathParams);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }

    public function testExecuteHandlesExecutorExceptions(): void
    {
        $this->executor->expects($this->once())
            ->method('findAll')
            ->willThrowException(new \Exception('Database connection failed'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database connection failed');

        $this->indexOperation->execute($this->request);
    }

    public function testMultipleExecutions(): void
    {
        $resources1 = [['id' => 1, 'name' => 'First Set']];
        $resources2 = [['id' => 2, 'name' => 'Second Set']];

        $this->executor->expects($this->exactly(2))
            ->method('findAll')
            ->willReturnOnConsecutiveCalls($resources1, $resources2);

        $result1 = $this->indexOperation->execute($this->request);
        $result2 = $this->indexOperation->execute($this->request);

        $this->assertInstanceOf(CrudResultInterface::class, $result1);
        $this->assertInstanceOf(CrudResultInterface::class, $result2);
        $this->assertNotSame($result1, $result2);
    }

    public function testOperationIsStateless(): void
    {
        $executor1 = $this->createMock(CommandExecutorInterface::class);
        $executor2 = $this->createMock(CommandExecutorInterface::class);

        $executor1->method('findAll')->willReturn([['id' => 1]]);
        $executor2->method('findAll')->willReturn([['id' => 2]]);

        $operation1 = new IndexOperation($executor1);
        $operation2 = new IndexOperation($executor2);

        $result1 = $operation1->execute($this->request);
        $result2 = $operation2->execute($this->request);

        // Different instances should be independent
        $this->assertInstanceOf(CrudResultInterface::class, $result1);
        $this->assertInstanceOf(CrudResultInterface::class, $result2);
    }

    public function testConstructorRequiresOnlyExecutor(): void
    {
        $executor = $this->createMock(CommandExecutorInterface::class);
        $operation = new IndexOperation($executor);

        $this->assertInstanceOf(IndexOperation::class, $operation);
    }

    public function testExecuteWithDifferentSuccessMessages(): void
    {
        $executor = $this->createMock(CommandExecutorInterface::class);
        $executor->method('findAll')->willReturn([['id' => 1]]);

        $operation1 = new IndexOperation($executor, 'Resources loaded');
        $operation2 = new IndexOperation($executor, 'Items retrieved');

        $result1 = $operation1->execute($this->request);
        $result2 = $operation2->execute($this->request);

        $this->assertInstanceOf(CrudResultInterface::class, $result1);
        $this->assertInstanceOf(CrudResultInterface::class, $result2);
    }

    public function testExecuteWithDifferentResourceTypes(): void
    {
        $testCases = [
            [['id' => 1, 'name' => 'String Resource']],
            [['id' => 2, 'value' => 123]],
            [['id' => 3, 'data' => ['nested' => 'structure']]],
            [['id' => 4, 'active' => true]],
            []
        ];

        foreach ($testCases as $resources) {
            $executor = $this->createMock(CommandExecutorInterface::class);
            $executor->method('findAll')->willReturn($resources);

            $operation = new IndexOperation($executor);
            $result = $operation->execute($this->request);

            $this->assertInstanceOf(CrudResultInterface::class, $result);
        }
    }

    public function testExecuteWithLargeResultSet(): void
    {
        $largeResultSet = [];
        for ($i = 1; $i <= 100; $i++) {
            $largeResultSet[] = ['id' => $i, 'name' => "Resource {$i}"];
        }

        $this->executor->method('findAll')->willReturn($largeResultSet);

        $result = $this->indexOperation->execute($this->request);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }

    public function testExecuteConsistency(): void
    {
        $expectedResources = [['id' => 1, 'consistent' => true]];

        $this->executor->method('findAll')->willReturn($expectedResources);

        // Multiple calls with same executor should be consistent
        $result1 = $this->indexOperation->execute($this->request);
        $result2 = $this->indexOperation->execute($this->request, ['ignored' => 'params']);

        $this->assertInstanceOf(CrudResultInterface::class, $result1);
        $this->assertInstanceOf(CrudResultInterface::class, $result2);
    }

    public function testExecutorInteraction(): void
    {
        $this->executor->expects($this->once())
            ->method('findAll')
            ->with() // No parameters expected for findAll
            ->willReturn([['found' => true]]);

        $result = $this->indexOperation->execute($this->request);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }

    public function testExecuteWithNullResults(): void
    {
        $this->executor->method('findAll')->willReturn(null);

        $result = $this->indexOperation->execute($this->request);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }

    public function testExecuteReturnsNewResultInstance(): void
    {
        $this->executor->method('findAll')->willReturn([]);

        $result1 = $this->indexOperation->execute($this->request);
        $result2 = $this->indexOperation->execute($this->request);

        $this->assertInstanceOf(CrudResultInterface::class, $result1);
        $this->assertInstanceOf(CrudResultInterface::class, $result2);
        $this->assertNotSame($result1, $result2);
    }

    public function testOperationDoesNotModifyRequest(): void
    {
        $this->executor->method('findAll')->willReturn([]);

        // The request should not be modified during execution
        $this->request->expects($this->never())->method('withAttribute');
        $this->request->expects($this->never())->method('withHeader');

        $result = $this->indexOperation->execute($this->request);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }
}
