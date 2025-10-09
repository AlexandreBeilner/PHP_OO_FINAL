<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Controllers\Crud\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Controllers\Crud\Impl\CrudResult;
use App\Application\Shared\Controllers\Crud\CrudResultInterface;

final class CrudResultTest extends TestCase
{
    public function testImplementsCrudResultInterface(): void
    {
        $result = new CrudResult('data', 'message', 200);

        $this->assertInstanceOf(CrudResultInterface::class, $result);
    }

    public function testConstructorSetsAllProperties(): void
    {
        $data = ['id' => 1, 'name' => 'Test'];
        $message = 'Operation successful';
        $code = 201;
        $meta = ['count' => 5, 'page' => 1];

        $result = new CrudResult($data, $message, $code, $meta);

        $this->assertEquals($data, $result->getData());
        $this->assertEquals($message, $result->getMessage());
        $this->assertEquals($code, $result->getCode());
        $this->assertTrue($result->hasMeta());
        $this->assertEquals($meta, $result->getMeta());
    }

    public function testConstructorWithDefaultMeta(): void
    {
        $result = new CrudResult('test data', 'success', 200);

        $this->assertEquals('test data', $result->getData());
        $this->assertEquals('success', $result->getMessage());
        $this->assertEquals(200, $result->getCode());
        $this->assertFalse($result->hasMeta());
        $this->assertEquals([], $result->getMeta());
    }

    public function testGetDataReturnsCorrectData(): void
    {
        $testData = 'string data';
        $result = new CrudResult($testData, 'message', 200);

        $this->assertEquals($testData, $result->getData());

        // Test with different data types
        $arrayData = ['key' => 'value'];
        $result2 = new CrudResult($arrayData, 'message', 200);
        $this->assertEquals($arrayData, $result2->getData());

        $nullData = null;
        $result3 = new CrudResult($nullData, 'message', 200);
        $this->assertNull($result3->getData());
    }

    public function testGetMessageReturnsCorrectMessage(): void
    {
        $message = 'Custom success message';
        $result = new CrudResult('data', $message, 200);

        $this->assertEquals($message, $result->getMessage());
    }

    public function testGetCodeReturnsCorrectCode(): void
    {
        $code = 404;
        $result = new CrudResult('not found', 'error', $code);

        $this->assertEquals($code, $result->getCode());
    }

    public function testHasMetaReturnsTrueWhenMetaExists(): void
    {
        $meta = ['pagination' => true];
        $result = new CrudResult('data', 'message', 200, $meta);

        $this->assertTrue($result->hasMeta());
    }

    public function testHasMetaReturnsFalseWhenMetaEmpty(): void
    {
        $result = new CrudResult('data', 'message', 200, []);

        $this->assertFalse($result->hasMeta());
    }

    public function testHasMetaReturnsFalseWhenMetaNotProvided(): void
    {
        $result = new CrudResult('data', 'message', 200);

        $this->assertFalse($result->hasMeta());
    }

    public function testGetMetaReturnsCorrectMeta(): void
    {
        $meta = [
            'total' => 100,
            'page' => 2,
            'per_page' => 10,
            'timestamps' => ['created' => '2023-01-01', 'updated' => '2023-01-02']
        ];

        $result = new CrudResult('data', 'message', 200, $meta);

        $this->assertEquals($meta, $result->getMeta());
    }

    public function testGetMetaReturnsEmptyArrayWhenNoMeta(): void
    {
        $result = new CrudResult('data', 'message', 200);

        $this->assertEquals([], $result->getMeta());
    }

    public function testWithDifferentDataTypes(): void
    {
        // Test with object data
        $objectData = new \stdClass();
        $objectData->id = 1;
        $result1 = new CrudResult($objectData, 'object test', 200);
        $this->assertEquals($objectData, $result1->getData());

        // Test with integer data
        $intData = 12345;
        $result2 = new CrudResult($intData, 'int test', 200);
        $this->assertEquals($intData, $result2->getData());

        // Test with boolean data
        $boolData = true;
        $result3 = new CrudResult($boolData, 'bool test', 200);
        $this->assertTrue($result3->getData());
    }

    public function testWithVariousStatusCodes(): void
    {
        $statusCodes = [200, 201, 400, 401, 403, 404, 422, 500];

        foreach ($statusCodes as $code) {
            $result = new CrudResult('data', 'message', $code);
            $this->assertEquals($code, $result->getCode(), "Status code {$code} should be preserved");
        }
    }

    public function testWithComplexMetadata(): void
    {
        $complexMeta = [
            'pagination' => [
                'current_page' => 1,
                'per_page' => 15,
                'total' => 150,
                'total_pages' => 10
            ],
            'filters' => [
                'status' => 'active',
                'category' => 'premium'
            ],
            'sorting' => [
                'field' => 'created_at',
                'direction' => 'desc'
            ]
        ];

        $result = new CrudResult(['items' => []], 'filtered results', 200, $complexMeta);

        $this->assertTrue($result->hasMeta());
        $this->assertEquals($complexMeta, $result->getMeta());
        $this->assertEquals(15, $result->getMeta()['pagination']['per_page']);
    }

    public function testImmutabilityOfData(): void
    {
        $originalData = ['id' => 1, 'name' => 'Original'];
        $result = new CrudResult($originalData, 'message', 200);

        // Modify the original data
        $originalData['id'] = 999;
        $originalData['name'] = 'Modified';

        // CrudResult should still have the original values
        $this->assertEquals(1, $result->getData()['id']);
        $this->assertEquals('Original', $result->getData()['name']);
    }

    public function testWithEmptyStringMessage(): void
    {
        $result = new CrudResult('data', '', 200);

        $this->assertEquals('', $result->getMessage());
        $this->assertIsString($result->getMessage());
    }

    public function testAllGettersReturnExpectedTypes(): void
    {
        $result = new CrudResult(['test'], 'message', 200, ['meta' => true]);

        $this->assertIsArray($result->getData());
        $this->assertIsString($result->getMessage());
        $this->assertIsInt($result->getCode());
        $this->assertIsBool($result->hasMeta());
        $this->assertIsArray($result->getMeta());
    }
}
