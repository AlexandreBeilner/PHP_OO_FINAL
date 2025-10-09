<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\DTOs\Impl;

use App\Application\Shared\DTOs\Impl\ApiResponse;
use PHPUnit\Framework\TestCase;

final class ApiResponseTest extends TestCase
{
    public function testAddMetaAddsKeyValueAndReturnsInstance(): void
    {
        $response = new ApiResponse(true, 'data', 'message');

        $result = $response->addMeta('key1', 'value1');
        $response->addMeta('key2', 'value2');

        $this->assertSame($response, $result);
        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $response->getMeta());
    }

    public function testAddMetaOverwritesExistingKey(): void
    {
        $response = new ApiResponse(true, 'data', 'message');

        $response->addMeta('key', 'value1');
        $response->addMeta('key', 'value2');

        $this->assertEquals(['key' => 'value2'], $response->getMeta());
    }

    public function testConstructorSetsAllProperties(): void
    {
        $data = ['user' => 'John Doe'];
        $message = 'Success message';
        $code = 201;
        $meta = ['timestamp' => '2025-01-01'];

        $response = new ApiResponse(true, $data, $message, $code, $meta);

        $this->assertTrue($response->isSuccess());
        $this->assertEquals($data, $response->getData());
        $this->assertEquals($message, $response->getMessage());
        $this->assertEquals($code, $response->getCode());
        $this->assertEquals($meta, $response->getMeta());
    }

    public function testConstructorWithDefaultValues(): void
    {
        $data = 'simple data';
        $message = 'Default message';

        $response = new ApiResponse(false, $data, $message);

        $this->assertFalse($response->isSuccess());
        $this->assertEquals($data, $response->getData());
        $this->assertEquals($message, $response->getMessage());
        $this->assertEquals(200, $response->getCode());
        $this->assertEquals([], $response->getMeta());
    }

    public function testGettersReturnCorrectValues(): void
    {
        $response = new ApiResponse(true, 'test', 'message', 500, ['debug' => true]);

        $this->assertTrue($response->isSuccess());
        $this->assertEquals('test', $response->getData());
        $this->assertEquals('message', $response->getMessage());
        $this->assertEquals(500, $response->getCode());
        $this->assertEquals(['debug' => true], $response->getMeta());
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        $data = ['test' => 'data'];
        $message = 'Test message';
        $code = 404;
        $meta = ['error' => 'Not found'];

        $response = new ApiResponse(false, $data, $message, $code, $meta);
        $array = $response->toArray();

        $expected = [
            'success' => false,
            'data' => $data,
            'message' => $message,
            'code' => $code,
            'meta' => $meta,
        ];

        $this->assertEquals($expected, $array);
    }

    public function testToJsonReturnsValidJsonString(): void
    {
        $data = ['name' => 'Test'];
        $message = 'Success';

        $response = new ApiResponse(true, $data, $message, 200, ['time' => '12:00']);
        $json = $response->toJson();

        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertEquals($response->toArray(), $decoded);
    }

    public function testToJsonWithComplexData(): void
    {
        $complexData = [
            'users' => [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane'],
            ],
            'pagination' => ['total' => 2, 'page' => 1],
        ];

        $response = new ApiResponse(true, $complexData, 'Users retrieved');
        $json = $response->toJson();

        $decoded = json_decode($json, true);
        $this->assertEquals($complexData, $decoded['data']);
    }

    public function testToJsonWithNullData(): void
    {
        $response = new ApiResponse(true, null, 'message');
        $json = $response->toJson();

        $decoded = json_decode($json, true);
        $this->assertNull($decoded['data']);
    }
}
