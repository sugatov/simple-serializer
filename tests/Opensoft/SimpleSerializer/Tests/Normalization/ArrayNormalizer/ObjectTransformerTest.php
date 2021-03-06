<?php
/**
 * This file is part of the Simple Serializer.
 *
 * Copyright (c) 2014 Farheap Solutions (http://www.farheap.com)
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Opensoft\SimpleSerializer\Tests\Normalization\ArrayNormalizer;

use Opensoft\SimpleSerializer\Normalization\ArrayNormalizer\ObjectTransformer;
use Opensoft\SimpleSerializer\Tests\Metadata\Driver\Fixture\A\A;
use Opensoft\SimpleSerializer\Tests\Metadata\Driver\Fixture\A\AChildren;
use Opensoft\SimpleSerializer\Metadata\PropertyMetadata;



/**
 * @author Anton Konovalov <anton.konovalov@opensoftdev.ru>
 */
class ObjectTransformerTest extends BaseTest
{
    /**
     * @var ObjectTransformer
     */
    private $transformer;

    /**
     * @dataProvider childrenDataProvider
     * @param AChildren $aChildren
     * @param array $aChildrenAsArray
     */
    public function testNormalization(AChildren $aChildren,array $aChildrenAsArray)
    {
        $normalizedChildren = $this->transformer->normalize($aChildren, new PropertyMetadata('children'));
        $this->assertEquals($aChildrenAsArray, $normalizedChildren);
    }

    /**
     * @dataProvider childrenDataProvider
     * @param AChildren $aChildren
     * @param array $aChildrenAsArray
     */
    public function testDenormalization(AChildren $aChildren,array $aChildrenAsArray)
    {
        $object = new AChildren();
        $property = $this->makeSimpleProperty('children', 'Opensoft\SimpleSerializer\Tests\Metadata\Driver\Fixture\A\AChildren');
        $this->transformer->denormalize($aChildrenAsArray, $property, $object);
        $this->assertEquals($aChildren, $object);

        $object = $this->transformer->denormalize($aChildrenAsArray, $property, 1);
        $this->assertEquals($aChildren, $object);

        $object = $this->transformer->denormalize($aChildrenAsArray, $property, new A(), true);
        $this->assertEquals($aChildren, $object);
    }

    /**
     * @dataProvider childrenDataProvider
     * @param AChildren $aChildren
     * @param array $aChildrenAsArray
     */
    public function testSupportValue(AChildren $aChildren,array $aChildrenAsArray)
    {
        $this->assertTrue($this->transformer->supportValueForNormalization($aChildren));
        $this->assertTrue($this->transformer->supportValueForDenormalization($aChildrenAsArray));
        $this->assertFalse($this->transformer->supportValueForNormalization($aChildrenAsArray));
        $this->assertFalse($this->transformer->supportValueForDenormalization($aChildren));
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initializeNormalizer();
        $this->transformer = new ObjectTransformer($this->normalizer);
    }
}
