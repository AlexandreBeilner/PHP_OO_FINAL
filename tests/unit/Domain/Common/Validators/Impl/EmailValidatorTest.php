<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Common\Validators\Impl;

use App\Domain\Common\Validators\Impl\EmailValidator;
use PHPUnit\Framework\TestCase;

final class EmailValidatorTest extends TestCase
{
    private EmailValidator $validator;

    public function testGetErrorMessageAfterEmptyValidation(): void
    {
        $this->validator->validate('');
        $this->assertEquals('Email não pode estar vazio', $this->validator->getErrorMessage());
    }

    public function testGetErrorMessageAfterInvalidFormatValidation(): void
    {
        $this->validator->validate('invalid-email');
        $this->assertEquals('Formato de email inválido', $this->validator->getErrorMessage());
    }

    public function testGetErrorMessageAfterNonStringValidation(): void
    {
        $this->validator->validate(123);
        $this->assertEquals('Email deve ser uma string', $this->validator->getErrorMessage());
    }

    public function testIsValidFormatWithInvalidEmails(): void
    {
        $this->assertFalse($this->validator->isValidFormat('invalid-email'));
        $this->assertFalse($this->validator->isValidFormat('user@'));
        $this->assertFalse($this->validator->isValidFormat('@domain.com'));
    }

    public function testIsValidFormatWithValidEmails(): void
    {
        $this->assertTrue($this->validator->isValidFormat('test@example.com'));
        $this->assertTrue($this->validator->isValidFormat('user.name+tag@domain.co.uk'));
    }

    public function testNormalizeConvertsToLowercaseAndTrims(): void
    {
        $this->assertEquals('user@example.com', $this->validator->normalize('USER@EXAMPLE.COM'));
        $this->assertEquals('test@domain.com', $this->validator->normalize('  TEST@DOMAIN.COM  '));
        $this->assertEquals('mixed@case.com', $this->validator->normalize('MiXeD@CaSe.CoM'));
    }

    public function testValidateWithEmptyStringReturnsFalse(): void
    {
        $this->assertFalse($this->validator->validate(''));
        $this->assertFalse($this->validator->validate('   '));
    }

    public function testValidateWithInvalidEmailReturnsFalse(): void
    {
        $this->assertFalse($this->validator->validate('invalid-email'));
        $this->assertFalse($this->validator->validate('missing@.com'));
        $this->assertFalse($this->validator->validate('@domain.com'));
        $this->assertFalse($this->validator->validate('user@'));
        $this->assertFalse($this->validator->validate('user name@domain.com'));
    }

    public function testValidateWithNonStringReturnsFalse(): void
    {
        $this->assertFalse($this->validator->validate(123));
        $this->assertFalse($this->validator->validate(null));
        $this->assertFalse($this->validator->validate([]));
        $this->assertFalse($this->validator->validate(true));
    }

    public function testValidateWithValidEmailReturnsTrue(): void
    {
        $this->assertTrue($this->validator->validate('valid@example.com'));
        $this->assertTrue($this->validator->validate('user.name@domain.co.uk'));
        $this->assertTrue($this->validator->validate('test+123@gmail.com'));
    }

    protected function setUp(): void
    {
        $this->validator = new EmailValidator();
    }
}
