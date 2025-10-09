<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Modules\Security\Factories\Impl;

use App\Application\Modules\Security\Factories\Impl\UserRequestValidator;
use App\Application\Shared\Controllers\Crud\RequestValidatorInterface;
use App\Domain\Security\Commands\Impl\CreateUserCommand;
use App\Domain\Security\Commands\Impl\UpdateUserCommand;
use App\Domain\Security\DTOs\Impl\CreateUserDataDTO;
use App\Domain\Security\DTOs\Impl\UpdateUserDataDTO;
use App\Domain\Security\Services\UserValidationServiceInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class UserRequestValidatorTest extends TestCase
{
    private UserRequestValidator $validator;
    private UserValidationServiceInterface $userValidationService;

    protected function setUp(): void
    {
        $this->userValidationService = $this->createMock(UserValidationServiceInterface::class);
        $this->validator = new UserRequestValidator($this->userValidationService);
    }

    public function testImplementsRequestValidatorInterface(): void
    {
        $this->assertInstanceOf(RequestValidatorInterface::class, $this->validator);
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $userValidationService = $this->createMock(UserValidationServiceInterface::class);
        $instance = new UserRequestValidator($userValidationService);

        $this->assertInstanceOf(UserRequestValidator::class, $instance);
    }

    public function testValidateCreateCommandCallsUserValidationService(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $dto = CreateUserDataDTO::fromArray(['username' => 'test', 'email' => 'test@test.com', 'password' => 'password123']);
        $expectedCommand = new CreateUserCommand($dto);

        $this->userValidationService
            ->expects($this->once())
            ->method('validateCreateUserCommand')
            ->with($request)
            ->willReturn($expectedCommand);

        $result = $this->validator->validateCreateCommand($request);

        $this->assertInstanceOf(CreateUserCommand::class, $result);
        $this->assertEquals($expectedCommand, $result);
    }

    public function testValidateUpdateCommandCallsUserValidationService(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $dto = UpdateUserDataDTO::fromArray(['username' => 'updated', 'email' => 'updated@test.com']);
        $expectedCommand = new UpdateUserCommand($dto);

        $this->userValidationService
            ->expects($this->once())
            ->method('validateUpdateUserCommand')
            ->with($request)
            ->willReturn($expectedCommand);

        $result = $this->validator->validateUpdateCommand($request);

        $this->assertInstanceOf(UpdateUserCommand::class, $result);
        $this->assertEquals($expectedCommand, $result);
    }

    public function testValidateCreateCommandPassesRequestCorrectly(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $dto = CreateUserDataDTO::fromArray(['username' => 'test', 'email' => 'test@test.com', 'password' => 'password123']);
        $command = new CreateUserCommand($dto);

        $this->userValidationService
            ->expects($this->once())
            ->method('validateCreateUserCommand')
            ->with($this->identicalTo($request))
            ->willReturn($command);

        $this->validator->validateCreateCommand($request);
    }

    public function testValidateUpdateCommandPassesRequestCorrectly(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $dto = UpdateUserDataDTO::fromArray(['username' => 'updated', 'email' => 'updated@test.com']);
        $command = new UpdateUserCommand($dto);

        $this->userValidationService
            ->expects($this->once())
            ->method('validateUpdateUserCommand')
            ->with($this->identicalTo($request))
            ->willReturn($command);

        $this->validator->validateUpdateCommand($request);
    }

    public function testValidateCreateCommandReturnsExactServiceResult(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $dto = CreateUserDataDTO::fromArray(['username' => 'exact', 'email' => 'exact@test.com', 'password' => 'password123']);
        $serviceCommand = new CreateUserCommand($dto);

        $this->userValidationService
            ->method('validateCreateUserCommand')
            ->willReturn($serviceCommand);

        $result = $this->validator->validateCreateCommand($request);

        $this->assertSame($serviceCommand, $result);
    }

    public function testValidateUpdateCommandReturnsExactServiceResult(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $dto = UpdateUserDataDTO::fromArray(['username' => 'exact', 'email' => 'exact@test.com']);
        $serviceCommand = new UpdateUserCommand($dto);

        $this->userValidationService
            ->method('validateUpdateUserCommand')
            ->willReturn($serviceCommand);

        $result = $this->validator->validateUpdateCommand($request);

        $this->assertSame($serviceCommand, $result);
    }

    public function testConstructorStoresDependencyCorrectly(): void
    {
        $userValidationService = $this->createMock(UserValidationServiceInterface::class);
        $validator = new UserRequestValidator($userValidationService);
        $request = $this->createMock(ServerRequestInterface::class);
        $dto = CreateUserDataDTO::fromArray(['username' => 'test', 'email' => 'test@test.com', 'password' => 'password123']);
        $command = new CreateUserCommand($dto);
        
        $userValidationService
            ->expects($this->once())
            ->method('validateCreateUserCommand')
            ->willReturn($command);

        $validator->validateCreateCommand($request);
    }

    public function testValidatorActsAsProxy(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $createDto = CreateUserDataDTO::fromArray(['username' => 'create', 'email' => 'create@test.com', 'password' => 'password123']);
        $updateDto = UpdateUserDataDTO::fromArray(['username' => 'update', 'email' => 'update@test.com']);
        $createCommand = new CreateUserCommand($createDto);
        $updateCommand = new UpdateUserCommand($updateDto);

        $this->userValidationService
            ->expects($this->once())
            ->method('validateCreateUserCommand')
            ->with($request)
            ->willReturn($createCommand);

        $this->userValidationService
            ->expects($this->once())
            ->method('validateUpdateUserCommand')
            ->with($request)
            ->willReturn($updateCommand);

        $createResult = $this->validator->validateCreateCommand($request);
        $updateResult = $this->validator->validateUpdateCommand($request);

        $this->assertEquals($createCommand, $createResult);
        $this->assertEquals($updateCommand, $updateResult);
    }
}