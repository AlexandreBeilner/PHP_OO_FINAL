<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Exceptions\Impl;

use App\Application\Shared\Exceptions\DatabaseExceptionInterface;
use App\Application\Shared\Exceptions\Impl\DatabaseException;
use PHPUnit\Framework\TestCase;

final class DatabaseExceptionTest extends TestCase
{
    public function testConstructorSetsDefaultValues(): void
    {
        $exception = new DatabaseException();

        $this->assertEquals('Database error', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
        $this->assertEquals([], $exception->getParameters());
        $this->assertNull($exception->getQuery());
    }

    public function testConstructorWithCustomValues(): void
    {
        $message = 'Connection failed';
        $query = 'SELECT * FROM users WHERE id = ?';
        $parameters = [42];
        $code = 503;

        $exception = new DatabaseException($message, $query, $parameters, $code);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals($parameters, $exception->getParameters());
        $this->assertEquals($query, $exception->getQuery());
    }

    public function testConstructorWithParametersOnly(): void
    {
        $parameters = ['John Doe', 'john@example.com', 'admin'];
        $exception = new DatabaseException('Query failed', null, $parameters);

        $this->assertEquals('Query failed', $exception->getMessage());
        $this->assertNull($exception->getQuery());
        $this->assertEquals($parameters, $exception->getParameters());
        $this->assertEquals(500, $exception->getCode());
    }

    public function testConstructorWithQueryOnly(): void
    {
        $query = 'INSERT INTO users (name, email) VALUES (?, ?)';
        $exception = new DatabaseException('Insert failed', $query);

        $this->assertEquals('Insert failed', $exception->getMessage());
        $this->assertEquals($query, $exception->getQuery());
        $this->assertEquals([], $exception->getParameters());
        $this->assertEquals(500, $exception->getCode());
    }

    public function testExceptionCanBeThrownAndCaught(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Database connection lost');
        $this->expectExceptionCode(503);

        throw new DatabaseException('Database connection lost', null, [], 503);
    }

    public function testExceptionWithComplexParameters(): void
    {
        $complexParams = [
            'filters' => ['status' => 'active', 'role' => 'admin'],
            'pagination' => ['limit' => 10, 'offset' => 20],
            'sort' => ['field' => 'created_at', 'direction' => 'DESC'],
        ];

        $exception = new DatabaseException('Complex query failed', 'SELECT * FROM users', $complexParams);

        $this->assertEquals($complexParams, $exception->getParameters());
        $this->assertEquals('SELECT * FROM users', $exception->getQuery());
    }

    public function testExceptionWithEmptyStringQuery(): void
    {
        $exception = new DatabaseException('Error', '');

        $this->assertEquals('', $exception->getQuery());
        $this->assertIsString($exception->getQuery());
    }

    public function testExceptionWorksWithTryCatch(): void
    {
        $query = 'DELETE FROM users WHERE id = ?';
        $parameters = [999];

        try {
            throw new DatabaseException('Delete failed', $query, $parameters, 400);
        } catch (DatabaseException $e) {
            $this->assertEquals('Delete failed', $e->getMessage());
            $this->assertEquals(400, $e->getCode());
            $this->assertEquals($query, $e->getQuery());
            $this->assertEquals($parameters, $e->getParameters());
            return;
        }

        $this->fail('Exception was not thrown or caught properly');
    }

    public function testGetParametersReturnsEmptyArrayWhenNoParameters(): void
    {
        $exception = new DatabaseException();

        $this->assertEquals([], $exception->getParameters());
        $this->assertIsArray($exception->getParameters());
    }

    public function testGetParametersReturnsSetParameters(): void
    {
        $parameters = [
            'user_id' => 123,
            'email' => 'test@example.com',
            'status' => 'active',
        ];

        $exception = new DatabaseException('Error', null, $parameters);

        $this->assertEquals($parameters, $exception->getParameters());
    }

    public function testGetQueryReturnsNullWhenNoQuery(): void
    {
        $exception = new DatabaseException();

        $this->assertNull($exception->getQuery());
    }

    public function testGetQueryReturnsSetQuery(): void
    {
        $query = 'UPDATE users SET status = ? WHERE id = ?';
        $exception = new DatabaseException('Update failed', $query);

        $this->assertEquals($query, $exception->getQuery());
    }

    public function testImplementsDatabaseExceptionInterface(): void
    {
        $exception = new DatabaseException();

        $this->assertInstanceOf(DatabaseExceptionInterface::class, $exception);
    }
}
