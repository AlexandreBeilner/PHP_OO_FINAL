<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Controllers\Crud\Impl\Operations;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Controllers\Crud\Impl\Operations\ShowOperation;
use App\Application\Shared\Controllers\Crud\CrudOperationInterface;
use App\Application\Shared\Controllers\Crud\CrudResultInterface;
use App\Application\Shared\Controllers\Crud\CommandExecutorInterface;
use Psr\Http\Message\ServerRequestInterface;
use InvalidArgumentException;

final class ShowOperationTest extends TestCase
{
    private ShowOperation $showOperation;
    private CommandExecutorInterface $executor;
    private ServerRequestInterface $request;

    protected function setUp(): void
    {
        $this->executor = $this->createMock(CommandExecutorInterface::class);
        $this->showOperation = new ShowOperation($this->executor);
        $this->request = $this->createMock(ServerRequestInterface::class);
    }

    public function testImplementsCrudOperationInterface(): void
    {
        $this->assertInstanceOf(CrudOperationInterface::class, $this->showOperation);
    }

    public function testConstructorWithDefaultMessage(): void
    {
        $operation = new ShowOperation($this->executor);

        $this->assertInstanceOf(ShowOperation::class, $operation);
        $this->assertInstanceOf(CrudOperationInterface::class, $operation);
    }

    public function testConstructorWithCustomMessage(): void
    {
        $operation = new ShowOperation($this->executor, 'Custom success message');

        $this->assertInstanceOf(ShowOperation::class, $operation);
        $this->assertInstanceOf(CrudOperationInterface::class, $operation);
    }

    public function testExecuteReturnsResourceWithPathParams(): void
    {
        $expectedResource = ['id' => 1, 'name' => 'Test Resource'];
        $pathParams = ['id' => '1'];

        $this->executor->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($expectedResource);

        $result = $this->showOperation->execute($this->request, $pathParams);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }

    public function testExecuteReturnsResourceWithRequestAttribute(): void
    {
        $expectedResource = ['id' => 2, 'name' => 'Test Resource 2'];

        $this->request->expects($this->once())
            ->method('getAttribute')
            ->with('id')
            ->willReturn('2');

        $this->executor->expects($this->once())
            ->method('findById')
            ->with(2)
            ->willReturn($expectedResource);

        $result = $this->showOperation->execute($this->request, []);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }

    public function testExecuteWithZeroIdThrowsException(): void
    {
        $pathParams = ['id' => '0'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ID é obrigatório para operação de busca');

        $this->showOperation->execute($this->request, $pathParams);
    }

    public function testExecuteWithNegativeIdThrowsException(): void
    {
        $pathParams = ['id' => '-1'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ID é obrigatório para operação de busca');

        $this->showOperation->execute($this->request, $pathParams);
    }

    public function testExecuteWithoutIdThrowsException(): void
    {
        $this->request->expects($this->once())
            ->method('getAttribute')
            ->with('id')
            ->willReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ID é obrigatório para operação de busca');

        $this->showOperation->execute($this->request, []);
    }

    public function testExecuteWithStringIdConvertsToInteger(): void
    {
        $expectedResource = ['id' => 123, 'name' => 'Test Resource'];
        $pathParams = ['id' => '123'];

        $this->executor->expects($this->once())
            ->method('findById')
            ->with(123)
            ->willReturn($expectedResource);

        $result = $this->showOperation->execute($this->request, $pathParams);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }

    public function testExecutePathParamsHavePriorityOverRequestAttribute(): void
    {
        $expectedResource = ['id' => 5, 'name' => 'From Path Params'];
        $pathParams = ['id' => '5'];

        // Request attribute should not be called since pathParams has id
        $this->request->expects($this->never())
            ->method('getAttribute');

        $this->executor->expects($this->once())
            ->method('findById')
            ->with(5)
            ->willReturn($expectedResource);

        $result = $this->showOperation->execute($this->request, $pathParams);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }

    public function testExecuteCallsFindByIdOnExecutor(): void
    {
        $resourceId = 42;
        $expectedResource = ['id' => $resourceId, 'data' => 'test'];
        $pathParams = ['id' => (string)$resourceId];

        $this->executor->expects($this->once())
            ->method('findById')
            ->with($resourceId)
            ->willReturn($expectedResource);

        $result = $this->showOperation->execute($this->request, $pathParams);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }

    public function testExecuteWithDifferentSuccessMessages(): void
    {
        $executor = $this->createMock(CommandExecutorInterface::class);
        $executor->method('findById')->willReturn(['id' => 1]);

        $operation1 = new ShowOperation($executor, 'Found successfully');
        $operation2 = new ShowOperation($executor, 'Resource located');

        // Both operations should work with different messages
        $result1 = $operation1->execute($this->request, ['id' => '1']);
        $result2 = $operation2->execute($this->request, ['id' => '1']);

        $this->assertInstanceOf(CrudResultInterface::class, $result1);
        $this->assertInstanceOf(CrudResultInterface::class, $result2);
    }

    public function testExecuteHandlesExecutorExceptions(): void
    {
        $pathParams = ['id' => '1'];

        $this->executor->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willThrowException(new \Exception('Resource not found'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Resource not found');

        $this->showOperation->execute($this->request, $pathParams);
    }

    public function testExecuteWithVariousIdFormats(): void
    {
        $testCases = [
            ['10', 10],
            ['0001', 1],
            ['99999', 99999]
        ];

        foreach ($testCases as [$stringId, $expectedIntId]) {
            $executor = $this->createMock(CommandExecutorInterface::class);
            $executor->expects($this->once())
                ->method('findById')
                ->with($expectedIntId)
                ->willReturn(['id' => $expectedIntId]);

            $operation = new ShowOperation($executor);
            $result = $operation->execute($this->request, ['id' => $stringId]);

            $this->assertInstanceOf(CrudResultInterface::class, $result);
        }
    }

    public function testMultipleExecutions(): void
    {
        $this->executor->expects($this->exactly(2))
            ->method('findById')
            ->willReturnOnConsecutiveCalls(
                ['id' => 1, 'name' => 'First'],
                ['id' => 2, 'name' => 'Second']
            );

        $result1 = $this->showOperation->execute($this->request, ['id' => '1']);
        $result2 = $this->showOperation->execute($this->request, ['id' => '2']);

        $this->assertInstanceOf(CrudResultInterface::class, $result1);
        $this->assertInstanceOf(CrudResultInterface::class, $result2);
        $this->assertNotSame($result1, $result2);
    }

    public function testOperationIsStateless(): void
    {
        $executor1 = $this->createMock(CommandExecutorInterface::class);
        $executor2 = $this->createMock(CommandExecutorInterface::class);

        $executor1->method('findById')->willReturn(['id' => 1]);
        $executor2->method('findById')->willReturn(['id' => 2]);

        $operation1 = new ShowOperation($executor1);
        $operation2 = new ShowOperation($executor2);

        $result1 = $operation1->execute($this->request, ['id' => '1']);
        $result2 = $operation2->execute($this->request, ['id' => '2']);

        // Different instances should be independent
        $this->assertInstanceOf(CrudResultInterface::class, $result1);
        $this->assertInstanceOf(CrudResultInterface::class, $result2);
    }

    public function testConstructorAcceptsOnlyRequiredParameters(): void
    {
        $executor = $this->createMock(CommandExecutorInterface::class);
        $operation = new ShowOperation($executor);

        $this->assertInstanceOf(ShowOperation::class, $operation);
    }

    public function testExecuteWithEmptyPathParamsUsesRequestAttribute(): void
    {
        $this->request->expects($this->once())
            ->method('getAttribute')
            ->with('id')
            ->willReturn('7');

        $this->executor->expects($this->once())
            ->method('findById')
            ->with(7)
            ->willReturn(['id' => 7]);

        $result = $this->showOperation->execute($this->request, []);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }
}
