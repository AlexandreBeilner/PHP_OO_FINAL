<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Controllers\Helpers\Impl;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Controllers\Helpers\Impl\ResponseHelper;
use App\Application\Shared\Controllers\Helpers\ResponseHelperInterface;
use App\Application\Shared\DTOs\Impl\ApiResponse;

final class ResponseHelperTest extends TestCase
{
    public function testImplementsResponseHelperInterface(): void
    {
        // Since ResponseHelper only has static methods, we test the class definition
        $reflection = new \ReflectionClass(ResponseHelper::class);
        
        $this->assertTrue($reflection->implementsInterface(ResponseHelperInterface::class));
    }

    public function testErrorReturnsApiResponse(): void
    {
        $result = ResponseHelper::error('Test error message');

        $this->assertInstanceOf(ApiResponse::class, $result);
    }

    public function testErrorWithDefaultParameters(): void
    {
        $result = ResponseHelper::error('Test error');

        $this->assertInstanceOf(ApiResponse::class, $result);
        $this->assertEquals('Test error', $result->getMessage());
        $this->assertEquals(400, $result->getCode());
        $this->assertFalse($result->isSuccess());
    }

    public function testErrorWithCustomCodeAndData(): void
    {
        $data = ['field' => 'value'];
        $result = ResponseHelper::error('Custom error', 422, $data);

        $this->assertInstanceOf(ApiResponse::class, $result);
        $this->assertEquals('Custom error', $result->getMessage());
        $this->assertEquals(422, $result->getCode());
        $this->assertEquals($data, $result->getData());
        $this->assertFalse($result->isSuccess());
    }

    public function testForbiddenReturnsApiResponse(): void
    {
        $result = ResponseHelper::forbidden();

        $this->assertInstanceOf(ApiResponse::class, $result);
        $this->assertEquals('Forbidden', $result->getMessage());
        $this->assertEquals(403, $result->getCode());
        $this->assertFalse($result->isSuccess());
        $this->assertNull($result->getData());
    }

    public function testForbiddenWithCustomMessage(): void
    {
        $result = ResponseHelper::forbidden('Access denied');

        $this->assertInstanceOf(ApiResponse::class, $result);
        $this->assertEquals('Access denied', $result->getMessage());
        $this->assertEquals(403, $result->getCode());
        $this->assertFalse($result->isSuccess());
    }

    public function testNotFoundReturnsApiResponse(): void
    {
        $result = ResponseHelper::notFound();

        $this->assertInstanceOf(ApiResponse::class, $result);
        $this->assertEquals('Resource not found', $result->getMessage());
        $this->assertEquals(404, $result->getCode());
        $this->assertFalse($result->isSuccess());
        $this->assertNull($result->getData());
    }

    public function testNotFoundWithCustomMessage(): void
    {
        $result = ResponseHelper::notFound('User not found');

        $this->assertInstanceOf(ApiResponse::class, $result);
        $this->assertEquals('User not found', $result->getMessage());
        $this->assertEquals(404, $result->getCode());
        $this->assertFalse($result->isSuccess());
    }

    public function testPaginatedReturnsApiResponse(): void
    {
        $data = [['id' => 1], ['id' => 2]];
        $result = ResponseHelper::paginated($data, 1, 10, 25);

        $this->assertInstanceOf(ApiResponse::class, $result);
        $this->assertEquals('Success', $result->getMessage());
        $this->assertEquals(200, $result->getCode());
        $this->assertTrue($result->isSuccess());
    }

    public function testPaginatedStructure(): void
    {
        $data = [['id' => 1], ['id' => 2]];
        $result = ResponseHelper::paginated($data, 2, 5, 23);

        $responseData = $result->getData();
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('pagination', $responseData);
        
        $pagination = $responseData['pagination'];
        $this->assertEquals(2, $pagination['page']);
        $this->assertEquals(5, $pagination['limit']);
        $this->assertEquals(23, $pagination['total']);
        $this->assertEquals(5, $pagination['pages']); // ceil(23/5) = 5
        $this->assertEquals($data, $responseData['data']);
    }

    public function testPaginatedWithZeroTotal(): void
    {
        $result = ResponseHelper::paginated([], 1, 10, 0);

        $responseData = $result->getData();
        $pagination = $responseData['pagination'];
        $this->assertEquals(0, $pagination['pages']); // ceil(0/10) = 0
    }

    public function testServerErrorReturnsApiResponse(): void
    {
        $result = ResponseHelper::serverError();

        $this->assertInstanceOf(ApiResponse::class, $result);
        $this->assertEquals('Internal server error', $result->getMessage());
        $this->assertEquals(500, $result->getCode());
        $this->assertFalse($result->isSuccess());
        $this->assertNull($result->getData());
    }

    public function testServerErrorWithCustomMessage(): void
    {
        $result = ResponseHelper::serverError('Database connection failed');

        $this->assertInstanceOf(ApiResponse::class, $result);
        $this->assertEquals('Database connection failed', $result->getMessage());
        $this->assertEquals(500, $result->getCode());
        $this->assertFalse($result->isSuccess());
    }

    public function testSuccessReturnsApiResponse(): void
    {
        $result = ResponseHelper::success();

        $this->assertInstanceOf(ApiResponse::class, $result);
        $this->assertEquals('Success', $result->getMessage());
        $this->assertEquals(200, $result->getCode());
        $this->assertTrue($result->isSuccess());
        $this->assertNull($result->getData());
    }

    public function testSuccessWithDataAndCustomMessage(): void
    {
        $data = ['user' => ['name' => 'John']];
        $result = ResponseHelper::success($data, 'User created', 201);

        $this->assertInstanceOf(ApiResponse::class, $result);
        $this->assertEquals('User created', $result->getMessage());
        $this->assertEquals(201, $result->getCode());
        $this->assertTrue($result->isSuccess());
        $this->assertEquals($data, $result->getData());
    }

    public function testUnauthorizedReturnsApiResponse(): void
    {
        $result = ResponseHelper::unauthorized();

        $this->assertInstanceOf(ApiResponse::class, $result);
        $this->assertEquals('Unauthorized', $result->getMessage());
        $this->assertEquals(401, $result->getCode());
        $this->assertFalse($result->isSuccess());
        $this->assertNull($result->getData());
    }

    public function testUnauthorizedWithCustomMessage(): void
    {
        $result = ResponseHelper::unauthorized('Invalid token');

        $this->assertInstanceOf(ApiResponse::class, $result);
        $this->assertEquals('Invalid token', $result->getMessage());
        $this->assertEquals(401, $result->getCode());
        $this->assertFalse($result->isSuccess());
    }

    public function testValidationErrorReturnsApiResponse(): void
    {
        $errors = ['email' => ['Email is required'], 'name' => ['Name too short']];
        $result = ResponseHelper::validationError($errors);

        $this->assertInstanceOf(ApiResponse::class, $result);
        $this->assertEquals('Validation failed', $result->getMessage());
        $this->assertEquals(422, $result->getCode());
        $this->assertFalse($result->isSuccess());
    }

    public function testValidationErrorStructure(): void
    {
        $errors = ['email' => ['Invalid format']];
        $result = ResponseHelper::validationError($errors, 'Custom validation message');

        $this->assertEquals('Custom validation message', $result->getMessage());
        $responseData = $result->getData();
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertEquals($errors, $responseData['errors']);
    }

    public function testAllMethodsReturnSameApiResponseClass(): void
    {
        $methods = [
            ResponseHelper::error('test'),
            ResponseHelper::forbidden(),
            ResponseHelper::notFound(),
            ResponseHelper::paginated([], 1, 10, 0),
            ResponseHelper::serverError(),
            ResponseHelper::success(),
            ResponseHelper::unauthorized(),
            ResponseHelper::validationError([])
        ];

        foreach ($methods as $response) {
            $this->assertInstanceOf(ApiResponse::class, $response);
        }
    }

    public function testStaticMethodsAreStateless(): void
    {
        // Each call should produce independent results
        $result1 = ResponseHelper::success(['data1']);
        $result2 = ResponseHelper::success(['data2']);

        $this->assertNotSame($result1, $result2);
        $this->assertEquals(['data1'], $result1->getData());
        $this->assertEquals(['data2'], $result2->getData());
    }
}
