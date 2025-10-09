<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Common\Exceptions\Impl;

use App\Domain\Common\Exceptions\Impl\ValidationException;
use App\Domain\Common\Exceptions\ValidationExceptionInterface;
use PHPUnit\Framework\TestCase;

final class ValidationExceptionTest extends TestCase
{
    public function testAddErrorAddsNewFieldError(): void
    {
        $exception = new ValidationException();

        $result = $exception->addError('name', 'Name is required');

        $this->assertSame($exception, $result); // Returns self for fluent interface
        $this->assertTrue($exception->hasErrors());
        $this->assertEquals(['name' => ['Name is required']], $exception->getErrors());
    }

    public function testAddErrorAppendsToExistingFieldErrors(): void
    {
        $exception = new ValidationException();

        $exception->addError('name', 'Name is required');
        $exception->addError('name', 'Name must be at least 2 characters');

        $expected = ['name' => ['Name is required', 'Name must be at least 2 characters']];
        $this->assertEquals($expected, $exception->getErrors());
    }

    public function testAddErrorWorksWithMultipleFields(): void
    {
        $exception = new ValidationException();

        $exception
            ->addError('name', 'Name is required')
            ->addError('email', 'Email is invalid')
            ->addError('password', 'Password too short');

        $expected = [
            'name' => ['Name is required'],
            'email' => ['Email is invalid'],
            'password' => ['Password too short'],
        ];

        $this->assertEquals($expected, $exception->getErrors());
    }

    public function testConstructorSetsDefaultValues(): void
    {
        $exception = new ValidationException();

        $this->assertEquals('Validation failed', $exception->getMessage());
        $this->assertEquals(422, $exception->getCode());
        $this->assertEquals([], $exception->getErrors());
        $this->assertFalse($exception->hasErrors());
    }

    public function testConstructorWithCustomValues(): void
    {
        $message = 'Custom validation message';
        $errors = ['name' => ['Name is required'], 'email' => ['Email is invalid']];
        $code = 400;

        $exception = new ValidationException($message, $errors, $code);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals($errors, $exception->getErrors());
        $this->assertTrue($exception->hasErrors());
    }

    public function testExceptionCanBeThrownAndCaught(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Test validation error');
        $this->expectExceptionCode(422);

        throw new ValidationException('Test validation error');
    }

    public function testExceptionWorksWithTryCatch(): void
    {
        try {
            throw new ValidationException('Test error', ['field' => ['error message']], 400);
        } catch (ValidationException $e) {
            $this->assertEquals('Test error', $e->getMessage());
            $this->assertEquals(400, $e->getCode());
            $this->assertEquals(['field' => ['error message']], $e->getErrors());
            $this->assertTrue($e->hasErrors());
            return;
        }

        $this->fail('Exception was not thrown or caught properly');
    }

    public function testGetErrorsReturnsCurrentErrors(): void
    {
        $initialErrors = ['field1' => ['Error 1'], 'field2' => ['Error 2']];
        $exception = new ValidationException('Message', $initialErrors);

        $exception->addError('field3', 'Error 3');

        $expected = [
            'field1' => ['Error 1'],
            'field2' => ['Error 2'],
            'field3' => ['Error 3'],
        ];

        $this->assertEquals($expected, $exception->getErrors());
    }

    public function testGetErrorsReturnsEmptyArrayWhenNoErrors(): void
    {
        $exception = new ValidationException();

        $this->assertEquals([], $exception->getErrors());
        $this->assertIsArray($exception->getErrors());
    }

    public function testHasErrorsReturnsFalseWhenNoErrors(): void
    {
        $exception = new ValidationException();

        $this->assertFalse($exception->hasErrors());
    }

    public function testHasErrorsReturnsTrueAfterAddingError(): void
    {
        $exception = new ValidationException();

        $this->assertFalse($exception->hasErrors());

        $exception->addError('field', 'error');

        $this->assertTrue($exception->hasErrors());
    }

    public function testHasErrorsReturnsTrueWhenErrorsExist(): void
    {
        $exception = new ValidationException('Message', ['field' => ['error']]);

        $this->assertTrue($exception->hasErrors());
    }

    public function testImplementsValidationExceptionInterface(): void
    {
        $exception = new ValidationException();

        $this->assertInstanceOf(ValidationExceptionInterface::class, $exception);
    }
}
