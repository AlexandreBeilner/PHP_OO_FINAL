<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\EntityPaths;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\EntityPaths\EntityPaths;

final class EntityPathsTest extends TestCase
{
    public function testConstructorWithEmptyArray(): void
    {
        $entityPaths = new EntityPaths([]);

        $this->assertFalse($entityPaths->hasAnyPath());
        $this->assertEquals(0, $entityPaths->count());
        $this->assertEquals([], $entityPaths->toArray());
    }

    public function testConstructorWithSinglePath(): void
    {
        $paths = ['/path/to/entity'];
        $entityPaths = new EntityPaths($paths);

        $this->assertTrue($entityPaths->hasAnyPath());
        $this->assertEquals(1, $entityPaths->count());
        $this->assertEquals($paths, $entityPaths->toArray());
    }

    public function testConstructorWithMultiplePaths(): void
    {
        $paths = [
            '/path/to/entity1',
            '/path/to/entity2',
            '/path/to/entity3'
        ];
        $entityPaths = new EntityPaths($paths);

        $this->assertTrue($entityPaths->hasAnyPath());
        $this->assertEquals(3, $entityPaths->count());
        $this->assertEquals($paths, $entityPaths->toArray());
    }

    public function testConstructorRemovesDuplicates(): void
    {
        $pathsWithDuplicates = [
            '/path/to/entity1',
            '/path/to/entity2',
            '/path/to/entity1', // Duplicate
            '/path/to/entity3',
            '/path/to/entity2'  // Duplicate
        ];
        
        $expectedUniquePaths = [
            '/path/to/entity1',
            '/path/to/entity2',
            '/path/to/entity3'
        ];

        $entityPaths = new EntityPaths($pathsWithDuplicates);

        $this->assertEquals(3, $entityPaths->count());
        $this->assertEquals($expectedUniquePaths, array_values($entityPaths->toArray()));
    }

    public function testHasAnyPathReturnsTrueWhenPathsExist(): void
    {
        $entityPaths = new EntityPaths(['/some/path']);

        $this->assertTrue($entityPaths->hasAnyPath());
    }

    public function testHasAnyPathReturnsFalseWhenNoPaths(): void
    {
        $entityPaths = new EntityPaths([]);

        $this->assertFalse($entityPaths->hasAnyPath());
    }

    public function testCountReturnsCorrectNumber(): void
    {
        // Test empty array
        $entityPaths = new EntityPaths([]);
        $this->assertEquals(0, $entityPaths->count());

        // Test single path
        $entityPaths = new EntityPaths(['/path1']);
        $this->assertEquals(1, $entityPaths->count());

        // Test multiple paths
        $entityPaths = new EntityPaths(['/path1', '/path2']);
        $this->assertEquals(2, $entityPaths->count());

        // Test many paths
        $entityPaths = new EntityPaths(['/path1', '/path2', '/path3', '/path4', '/path5']);
        $this->assertEquals(5, $entityPaths->count());
    }

    public function testToArrayReturnsInternalArray(): void
    {
        $originalPaths = ['/entity1', '/entity2', '/entity3'];
        $entityPaths = new EntityPaths($originalPaths);

        $result = $entityPaths->toArray();

        $this->assertIsArray($result);
        $this->assertEquals($originalPaths, $result);
    }

    public function testToArrayReturnsArrayAfterDuplicateRemoval(): void
    {
        $pathsWithDuplicates = ['/path1', '/path2', '/path1', '/path3'];
        $entityPaths = new EntityPaths($pathsWithDuplicates);

        $result = $entityPaths->toArray();

        $this->assertIsArray($result);
        $this->assertEquals(['/path1', '/path2', '/path3'], array_values($result));
    }

    public function testImmutabilityOfPaths(): void
    {
        $originalPaths = ['/path1', '/path2'];
        $entityPaths = new EntityPaths($originalPaths);

        // Get the array and modify it
        $retrievedPaths = $entityPaths->toArray();
        $retrievedPaths[] = '/path3';

        // Original should be unchanged
        $this->assertEquals(2, $entityPaths->count());
        $this->assertEquals($originalPaths, $entityPaths->toArray());
    }

    public function testValueObjectBehavior(): void
    {
        $paths = ['/entity/path1', '/entity/path2'];
        
        $entityPaths1 = new EntityPaths($paths);
        $entityPaths2 = new EntityPaths($paths);

        // Both should have same data but be different instances
        $this->assertNotSame($entityPaths1, $entityPaths2);
        $this->assertEquals($entityPaths1->toArray(), $entityPaths2->toArray());
        $this->assertEquals($entityPaths1->count(), $entityPaths2->count());
        $this->assertEquals($entityPaths1->hasAnyPath(), $entityPaths2->hasAnyPath());
    }

    public function testWithComplexPaths(): void
    {
        $complexPaths = [
            'App\\Domain\\User\\Entities\\UserEntity',
            'App\\Domain\\Product\\Entities\\ProductEntity',
            'App\\Domain\\Order\\Entities\\OrderEntity',
            'App\\Infrastructure\\Database\\Entities\\LogEntity'
        ];

        $entityPaths = new EntityPaths($complexPaths);

        $this->assertTrue($entityPaths->hasAnyPath());
        $this->assertEquals(4, $entityPaths->count());
        $this->assertEquals($complexPaths, $entityPaths->toArray());
    }
}