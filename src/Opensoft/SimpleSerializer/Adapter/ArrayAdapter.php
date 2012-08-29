<?php

/**
 * This file is part of the Simple Serializer.
 *
 * Copyright (c) 2012 Farheap Solutions (http://www.farheap.com)
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Opensoft\SimpleSerializer\Adapter;

use Opensoft\SimpleSerializer\Adapter\ArrayAdapterInterface as BaseArrayAdapter;
use Opensoft\SimpleSerializer\Exception\InvalidArgumentException;
use Opensoft\SimpleSerializer\Exception\RecursionException;
use Opensoft\SimpleSerializer\Metadata\MetadataFactory;
use Opensoft\SimpleSerializer\Metadata\PropertyMetadata;
use DateTime;

/**
 * @author Dmitry Petrov <dmitry.petrov@opensoftdev.ru>
 */
class ArrayAdapter implements BaseArrayAdapter
{
    const DIRECTION_SERIALIZE = 1;
    const DIRECTION_UNSERIALIZE = 0;

    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

    /**
     * @param MetadataFactory $metadataFactory
     */
    public function __construct(MetadataFactory $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritDoc}
     *
     * @param object $object
     * @return mixed
     */
    public function toArray($object)
    {
        $result = array();
        $className = $this->getFullClassName($object);
        $metadata = $this->metadataFactory->getMetadataForClass($className);
        foreach ($metadata->getProperties() as $property) {
            if (!$property->isExpose()) {
                continue;
            }

            $value = call_user_func(array($object, 'get' . ucfirst($property->getName())));
            if ($value === $object) {
                throw new RecursionException(sprintf('Invalid self reference detected. %s::%s', $className, $property->getName()));
            }

            $value = $this->handleValue($value, $property, self::DIRECTION_SERIALIZE);

            $result[$property->getSerializedName()] = $value;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed $data
     * @param object $object
     */
    public function toObject(array $data, $object)
    {
        $className = $this->getFullClassName($object);
        $metadata = $this->metadataFactory->getMetadataForClass($className);
        foreach ($metadata->getProperties() as $property) {
            if (!$property->isExpose() || !isset($data[$property->getSerializedName()])) {
                continue;
            }
            $value = $this->handleValue($data[$property->getSerializedName()], $property, self::DIRECTION_UNSERIALIZE, $object);

            if ($value === $object) {
                throw new RecursionException(sprintf('Invalid self reference detected. %s::%s', $className, $property->getName()));
            }

            call_user_func_array(array($object, 'set' . ucfirst($property->getName())), array($value));
        }

        return $object;
    }

    /**
     * @param object $object
     * @return string
     * @throws InvalidArgumentException
     */
    private function getFullClassName($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException('Argument is not an object');
        }

        return get_class($object);
    }

    /**
     * @param $value
     * @param PropertyMetadata $property
     * @param $direct
     * @param null|mixed $object
     * @param bool $inner
     * @throws \Opensoft\SimpleSerializer\Exception\InvalidArgumentException
     * @return array|bool|float|int|string|null
     */
    private function handleValue($value, $property, $direct, $object = null, $inner = false)
    {
        $type = $property->getType();
        if ($value !== null) {
            if ($type === 'string') {
                $value = (string)$value;
            } elseif ($type === 'boolean') {
                $value = (boolean)$value;
            } elseif ($type === 'integer') {
                $value = (integer)$value;
            } elseif ($type === 'double') {
                $value = (double)$value;
            } elseif ($type === 'DateTime' || ($type[0] === 'D' && strpos($type, 'DateTime<') === 0)) {
                if ($direct == self::DIRECTION_SERIALIZE) {
                    $dateTimeFormat = DateTime::ISO8601;
                    if (preg_match('/DateTime<(?<type>[a-zA-Z0-9\,\.\s\-\:\/\\\]+)>/', $type, $matches)) {
                        $dateTimeFormat = $matches['type'];
                        if (defined('\DateTime::' . $dateTimeFormat)) {
                            $dateTimeFormat = constant('\DateTime::' . $dateTimeFormat);
                        }
                    }
                    $value = $value->format($dateTimeFormat);
                } elseif ($direct == self::DIRECTION_UNSERIALIZE) {
                    $value = new DateTime($value);
                }
            } elseif ($type === 'array' || ($type[0] === 'a' && strpos($type, 'array<') === 0)) {
                $tmpResult = array();
                $tmpType = new PropertyMetadata($property->getName());
                $tmpType->setExpose(true)->setSerializedName($property->getSerializedName());
                if (preg_match('/array<(?<type>[a-zA-Z\\\]+)>/', $type, $matches)) {
                    $tmpType->setType($matches['type']);
                }
                if ($direct == self::DIRECTION_UNSERIALIZE) {
                    $existsData = call_user_func(array($object, 'get' . ucfirst($property->getName())));
                }
                foreach ($value as $k => $v) {
                    $tmpObject = $object;
                    if ($direct == self::DIRECTION_UNSERIALIZE && isset($existsData[$k]) && is_object($existsData[$k])) {
                        $tmpObject = $existsData[$k];
                        $inner = true;
                    }
                    $v = $this->handleValue($v, $tmpType, $direct, $tmpObject, $inner);
                    $tmpResult[$k] = $v;
                    unset($tmpObject);
                }
                $value = $tmpResult;
                unset($tmpResult, $tmpType);
            } elseif (is_object($value) && $direct == self::DIRECTION_SERIALIZE) {
                $value = $this->toArray($value);
            } elseif (is_array($value) && $direct == self::DIRECTION_UNSERIALIZE) {
                if ($inner) {
                    $innerObject = $object;
                } else {
                    $innerObject = call_user_func(array($object, 'get' . ucfirst($property->getName())));
                }
                if (!is_object($innerObject) || !$innerObject instanceof $type) {
                    $innerObject = unserialize(sprintf('O:%d:"%s":0:{}', strlen($type), $type));
                }
                $value = $this->toObject($value, $innerObject);
            } elseif ($type !== null) {
                throw new InvalidArgumentException(sprintf('Unsupported type: %s', $type));
            }
        }

        return $value;
    }
}
