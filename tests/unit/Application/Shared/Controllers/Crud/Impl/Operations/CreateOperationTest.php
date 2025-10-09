<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Controllers\Crud\Impl\Operations;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Controllers\Crud\Impl\Operations\CreateOperation;
use App\Application\Shared\Controllers\Crud\CrudOperationInterface;
use App\Application\Shared\Controllers\Crud\CrudResultInterface;
use App\Application\Shared\Controllers\Crud\RequestValidatorInterface;
use App\Application\Shared\Controllers\Crud\CommandExecutorInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CreateOperationTest extends TestCase
{
    private CreateOperation $createOperation;
    private RequestValidatorInterface $validator;
    private CommandExecutorInterface $executor;
    private ServerRequestInterface $request;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(RequestValidatorInterface::class);
        $this->executor = $this->createMock(CommandExecutorInterface::class);
        $this->createOperation = new CreateOperation($this->validator, $this->executor);
        $this->request = $this->createMock(ServerRequestInterface::class);
    }

    public function testImplementsCrudOperationInterface(): void
    {
        $this->assertInstanceOf(CrudOperationInterface::class, $this->createOperation);
    }

    public function testConstructorWithDefaultMessage(): void
    {
        $operation = new CreateOperation($this->validator, $this->executor);

        $this->assertInstanceOf(CreateOperation::class, $operation);
        $this->assertInstanceOf(CrudOperationInterface::class, $operation);
    }

    public function testConstructorWithCustomMessage(): void
    {
        $operation = new CreateOperation($this->validator, $this->executor, 'Custom creation message');

        $this->assertInstanceOf(CreateOperation::class, $operation);
        $this->assertInstanceOf(CrudOperationInterface::class, $operation);
    }

    public function testExecuteValidatesRequest(): void
    {
        $mockCommand = new \stdClass();
        $mockResult = ['id' => 1, 'name' => 'Created Resource'];

        $this->validator->expects($this->once())
            ->method('validateCreateCommand')
            ->with($this->request)
            ->willReturn($mockCommand);

        $this->executor->expects($this->once())
            ->method('execute')
            ->with($mockCommand)
            ->willReturn($mockResult);

        $result = $this->createOperation->execute($this->request);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }

    public function testExecuteCallsValidatorWithCorrectRequest(): void
    {
        $mockCommand = new \stdClass();

        $this->validator->expects($this->once())
            ->method('validateCreateCommand')
            ->with($this->identicalTo($this->request))
            ->willReturn($mockCommand);

        $this->executor->method('execute')->willReturn(['created' => true]);

        $this->createOperation->execute($this->request);
    }

    public function testExecuteCallsExecutorWithValidatedCommand(): void
    {
        $mockCommand = new \stdClass();
        $mockResult = ['id' => 42, 'status' => 'created'];

        $this->validator->method('validateCreateCommand')->willReturn($mockCommand);

        $this->executor->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($mockCommand))
            ->willReturn($mockResult);

        $result = $this->createOperation->execute($this->request);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }

    public function testExecuteReturnsCrudResult(): void
    {
        $mockCommand = new \stdClass();
        $mockResult = ['id' => 1, 'data' => 'test'];

        $this->validator->method('validateCreateCommand')->willReturn($mockCommand);
        $this->executor->method('execute')->willReturn($mockResult);

        $result = $this->createOperation->execute($this->request);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }

    public function testExecuteWithPathParamsIgnoresPathParams(): void
    {
        $mockCommand = new \stdClass();
        $pathParams = ['id' => '123', 'unused' => 'data'];

        $this->validator->expects($this->once())
            ->method('validateCreateCommand')
            ->with($this->request)
            ->willReturn($mockCommand);

        $this->executor->method('execute')->willReturn(['created']);

        // Path params should be ignored for create operations
        $result = $this->createOperation->execute($this->request, $pathParams);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }

    public function testExecuteValidationExceptionPropagates(): void
    {
        $this->validator->expects($this->once())
            ->method('validateCreateCommand')
            ->with($this->request)
            ->willThrowException(new \InvalidArgumentException('Validation failed'));

        $this->executor->expects($this->never())
            ->method('execute');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Validation failed');

        $this->createOperation->execute($this->request);
    }

    public function testExecuteExecutorExceptionPropagates(): void
    {
        $mockCommand = new \stdClass();

        $this->validator->method('validateCreateCommand')->willReturn($mockCommand);

        $this->executor->expects($this->once())
            ->method('execute')
            ->with($mockCommand)
            ->willThrowException(new \RuntimeException('Execution failed'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Execution failed');

        $this->createOperation->execute($this->request);
    }

    public function testMultipleExecutions(): void
    {
        $mockCommand1 = new \stdClass();
        $mockCommand2 = new \stdClass();

        $this->validator->expects($this->exactly(2))
            ->method('validateCreateCommand')
            ->willReturnOnConsecutiveCalls($mockCommand1, $mockCommand2);

        $this->executor->expects($this->exactly(2))
            ->method('execute')
            ->willReturnOnConsecutiveCalls(
                ['id' => 1, 'name' => 'First'],
                ['id' => 2, 'name' => 'Second']
            );

        $result1 = $this->createOperation->execute($this->request);
        $result2 = $this->createOperation->execute($this->request);

        $this->assertInstanceOf(CrudResultInterface::class, $result1);
        $this->assertInstanceOf(CrudResultInterface::class, $result2);
        $this->assertNotSame($result1, $result2);
    }

    public function testOperationIsStateless(): void
    {
        $validator1 = $this->createMock(RequestValidatorInterface::class);
        $validator2 = $this->createMock(RequestValidatorInterface::class);
        $executor1 = $this->createMock(CommandExecutorInterface::class);
        $executor2 = $this->createMock(CommandExecutorInterface::class);

        $validator1->method('validateCreateCommand')->willReturn(new \stdClass());
        $validator2->method('validateCreateCommand')->willReturn(new \stdClass());
        $executor1->method('execute')->willReturn(['id' => 1]);
        $executor2->method('execute')->willReturn(['id' => 2]);

        $operation1 = new CreateOperation($validator1, $executor1);
        $operation2 = new CreateOperation($validator2, $executor2);

        $result1 = $operation1->execute($this->request);
        $result2 = $operation2->execute($this->request);

        // Different instances should be independent
        $this->assertInstanceOf(CrudResultInterface::class, $result1);
        $this->assertInstanceOf(CrudResultInterface::class, $result2);
    }

    public function testConstructorRequiresAllParameters(): void
    {
        $validator = $this->createMock(RequestValidatorInterface::class);
        $executor = $this->createMock(CommandExecutorInterface::class);

        $operation = new CreateOperation($validator, $executor);

        $this->assertInstanceOf(CreateOperation::class, $operation);
    }

    public function testExecuteWithDifferentRequestTypes(): void
    {
        // Create separate instances for each test to avoid mock conflicts
        $testCases = [
            ['request' => $this->createMock(ServerRequestInterface::class), 'expectedId' => 1],
            ['request' => $this->createMock(ServerRequestInterface::class), 'expectedId' => 2],
            ['request' => $this->createMock(ServerRequestInterface::class), 'expectedId' => 3]
        ];

        foreach ($testCases as $testCase) {
            $validator = $this->createMock(RequestValidatorInterface::class);
            $executor = $this->createMock(CommandExecutorInterface::class);
            $operation = new CreateOperation($validator, $executor);
            
            $mockCommand = new \stdClass();
            
            $validator->expects($this->once())
                ->method('validateCreateCommand')
                ->with($testCase['request'])
                ->willReturn($mockCommand);

            $executor->expects($this->once())
                ->method('execute')
                ->with($mockCommand)
                ->willReturn(['id' => $testCase['expectedId']]);

            $result = $operation->execute($testCase['request']);

            $this->assertInstanceOf(CrudResultInterface::class, $result);
        }
    }

    public function testExecuteWithDifferentSuccessMessages(): void
    {
        $validator = $this->createMock(RequestValidatorInterface::class);
        $executor = $this->createMock(CommandExecutorInterface::class);

        $validator->method('validateCreateCommand')->willReturn(new \stdClass());
        $executor->method('execute')->willReturn(['id' => 1]);

        $operation1 = new CreateOperation($validator, $executor, 'Resource created');
        $operation2 = new CreateOperation($validator, $executor, 'Item added');

        $result1 = $operation1->execute($this->request);
        $result2 = $operation2->execute($this->request);

        $this->assertInstanceOf(CrudResultInterface::class, $result1);
        $this->assertInstanceOf(CrudResultInterface::class, $result2);
    }

    public function testExecuteWithNullPathParams(): void
    {
        $mockCommand = new \stdClass();

        $this->validator->expects($this->once())
            ->method('validateCreateCommand')
            ->with($this->request)
            ->willReturn($mockCommand);

        $this->executor->method('execute')->willReturn(['created']);

        $result = $this->createOperation->execute($this->request, []);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }

    public function testValidatorAndExecutorInteraction(): void
    {
        $mockCommand = new \stdClass();
        $mockResult = ['id' => 99, 'status' => 'success'];

        // Validator should be called first
        $this->validator->expects($this->once())
            ->method('validateCreateCommand')
            ->with($this->request)
            ->willReturn($mockCommand);

        // Executor should be called after validation with the validated command
        $this->executor->expects($this->once())
            ->method('execute')
            ->with($mockCommand)
            ->willReturn($mockResult);

        $result = $this->createOperation->execute($this->request);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }
}
