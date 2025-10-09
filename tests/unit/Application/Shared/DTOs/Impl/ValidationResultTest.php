<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\DTOs\Impl;

use App\Application\Shared\DTOs\Impl\ValidationResult;
use PHPUnit\Framework\TestCase;

final class ValidationResultTest extends TestCase
{
    public function testConstructorWithInvalidStateAndErrors(): void
    {
        $errors = ['Email is required', 'Password too short'];
        $result = new ValidationResult(false, $errors);

        $this->assertFalse($result->isValid());
        $this->assertEquals($errors, $result->getErrors());
        $this->assertTrue($result->hasErrors());
        $this->assertEquals('Email is required', $result->getFirstError());
    }

    public function testConstructorWithValidState(): void
    {
        $result = new ValidationResult(true);

        $this->assertTrue($result->isValid());
        $this->assertEquals([], $result->getErrors());
        $this->assertFalse($result->hasErrors());
        $this->assertNull($result->getFirstError());
    }

    public function testConstructorWithValidStateButWithErrors(): void
    {
        $errors = ['Warning: deprecated field'];
        $result = new ValidationResult(true, $errors);

        $this->assertTrue($result->isValid());
        $this->assertEquals($errors, $result->getErrors());
        $this->assertTrue($result->hasErrors());
        $this->assertEquals('Warning: deprecated field', $result->getFirstError());
    }

    public function testGetFirstErrorWithAssociativeErrors(): void
    {
        $errors = ['email' => 'Invalid email', 'password' => 'Too short'];
        $result = new ValidationResult(false, $errors);

        $this->assertEquals('Invalid email', $result->getFirstError());
    }

    public function testGetFirstErrorWithEmptyErrors(): void
    {
        $result = new ValidationResult(false, []);

        $this->assertNull($result->getFirstError());
    }

    public function testGetFirstErrorWithMultipleErrors(): void
    {
        $errors = ['First error', 'Second error', 'Third error'];
        $result = new ValidationResult(false, $errors);

        $this->assertEquals('First error', $result->getFirstError());
    }

    public function testHasErrorsReturnsFalseWhenNoErrors(): void
    {
        $result = new ValidationResult(false, []);

        $this->assertFalse($result->hasErrors());
        $this->assertNull($result->getFirstError());
    }

    public function testHasErrorsReturnsTrueWhenErrorsExist(): void
    {
        $result = new ValidationResult(true, ['Some error']);

        $this->assertTrue($result->hasErrors());
        $this->assertEquals('Some error', $result->getFirstError());
    }

    public function testInvalidResultWithEmptyErrorsArray(): void
    {
        $result = new ValidationResult(false, []);

        $this->assertFalse($result->isValid());
        $this->assertFalse($result->hasErrors());
        $this->assertEquals([], $result->getErrors());
        $this->assertNull($result->getFirstError());
    }

    public function testValidResultWithoutExplicitErrors(): void
    {
        $result = new ValidationResult(true);

        $this->assertTrue($result->isValid());
        $this->assertFalse($result->hasErrors());
        $this->assertEquals([], $result->getErrors());
        $this->assertNull($result->getFirstError());
    }
}
