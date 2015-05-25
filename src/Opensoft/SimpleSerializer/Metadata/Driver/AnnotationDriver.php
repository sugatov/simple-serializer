<?php
namespace Opensoft\SimpleSerializer\Metadata\Driver;

use Opensoft\SimpleSerializer\Metadata\Driver\DriverInterface;
use Opensoft\SimpleSerializer\Metadata\PropertyMetadata;
use Opensoft\SimpleSerializer\Metadata\ClassMetadata;
use Opensoft\SimpleSerializer\Exception\RuntimeException;
use Doctrine\Common\Annotations\AnnotationReader;
use Opensoft\SimpleSerializer\Metadata\Annotations;

/**
 * @author Evgeny Sugatov
 */
class AnnotationDriver implements DriverInterface
{
    /**
     * @var AnnotationReader
     */
    protected $reader;

    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }

    public function loadMetadataForClass($className)
    {
        $metadata = new ClassMetadata($className);
        $reflectionClass = new \ReflectionClass($className);
        $reflectionProperties = $reflectionClass->getProperties();
        foreach ($reflectionProperties as $reflectionProperty) {
            $name = $reflectionProperty->getName();
            $pMetadata = new PropertyMetadata($name);
            $annotations = $this->reader->getPropertyAnnotations($reflectionProperty);
            foreach ($annotations as $annotation) {
                if ($annotation instanceof Annotations\Expose) {
                    $pMetadata->setExpose((bool) $annotation->value);
                } elseif ($annotation instanceof Annotations\Type) {
                    $pMetadata->setType((string) $annotation->value);
                } elseif ($annotation instanceof Annotations\Groups) {
                    $pMetadata->setGroups($annotation->value);
                } elseif ($annotation instanceof Annotations\SerializedName) {
                    $pMetadata->setSerializedName((string) $annotation->value);
                } elseif ($annotation instanceof Annotations\SinceVersion) {
                    $pMetadata->setSinceVersion((string) $annotation->value);
                } elseif ($annotation instanceof Annotations\UntilVersion) {
                    $pMetadata->setUntilVersion((string) $annotation->value);
                }
            }
            $metadata->addPropertyMetadata($pMetadata);
        }
        return $metadata;
    }
}
