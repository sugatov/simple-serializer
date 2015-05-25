<?php
namespace Opensoft\SimpleSerializer\Metadata\Annotations;

/**
 * @author Evgeny Sugatov
 * @Annotation
 * @Target("PROPERTY")
 */
final class SinceVersion
{
    /**
     * @Required
     * @var string
     */
    public $value;
}
