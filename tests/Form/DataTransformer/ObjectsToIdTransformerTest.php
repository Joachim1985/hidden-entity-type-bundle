<?php

declare(strict_types=1);

namespace Shapecode\Bundle\HiddenEntityTypeBundle\Tests\Form\DataTransformer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shapecode\Bundle\HiddenEntityTypeBundle\Form\DataTransformer\ObjectsToIdTransformer;
use Shapecode\Bundle\HiddenEntityTypeBundle\Tests\Model\TestFormModel;
use Shapecode\Bundle\HiddenEntityTypeBundle\Tests\Model\TestObject;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class ObjectsToIdTransformerTest extends TestCase
{
    public function testValidTransformation() : void
    {
        $object = new TestObject();
        $object->setName('test');

        // mock any dependencies
        $objectRepository = $this->createConfiguredMock(ObjectRepository::class, [
            'findBy' => [$object],
        ]);

        $registry = $this->createConfiguredMock(ManagerRegistry::class, [
            'getRepository' => $objectRepository,
        ]);

        $transformer = new ObjectsToIdTransformer($registry, TestObject::class, 'name');

        $transformed = $transformer->transform([$object]);
        $reversed    = $transformer->reverseTransform('test');

        self::assertEquals('test', $transformed);
        self::assertEquals([$object], $reversed);
    }

    public function testInvalidValidTransformation() : void
    {
        $object = new TestObject();
        $object->setName('test');

        // mock any dependencies
        $objectRepository = $this->createConfiguredMock(ObjectRepository::class, [
            'findBy' => [],
        ]);

        $registry = $this->createConfiguredMock(ManagerRegistry::class, [
            'getRepository' => $objectRepository,
        ]);

        $transformer = new ObjectsToIdTransformer($registry, TestObject::class, 'name');

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Can\'t find entity of class "Shapecode\Bundle\HiddenEntityTypeBundle\Tests\Model\TestObject" with property "name" = "test".');

        $transformed = $transformer->transform([$object]);
        $reversed    = $transformer->reverseTransform('test');

        self::assertEquals('test', $transformed);
        self::assertEquals(null, $reversed);
    }

    public function testInvalidProperty() : void
    {
        $registry = $this->createMock(ManagerRegistry::class);

        $this->expectException(NoSuchPropertyException::class);
        $this->expectExceptionMessage('property id is missing in class Shapecode\Bundle\HiddenEntityTypeBundle\Tests\Model\TestObject');

        new ObjectsToIdTransformer($registry, TestObject::class, 'id');
    }

    public function testInvalidObject() : void
    {
        $object = new TestFormModel();

        $registry = $this->createMock(ManagerRegistry::class);

        $transformer = new ObjectsToIdTransformer($registry, TestObject::class, 'name');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an instance of Shapecode\Bundle\HiddenEntityTypeBundle\Tests\Model\TestObject. Got: Shapecode\Bundle\HiddenEntityTypeBundle\Tests\Model\TestFormModel');

        $transformer->transform([$object]);
    }

    public function testInvalidArray() : void
    {
        $object = new TestFormModel();

        $registry = $this->createMock(ManagerRegistry::class);

        $transformer = new ObjectsToIdTransformer($registry, TestObject::class, 'name');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an array. Got: Shapecode\Bundle\HiddenEntityTypeBundle\Tests\Model\TestFormModel');

        $transformer->transform($object);
    }
}
