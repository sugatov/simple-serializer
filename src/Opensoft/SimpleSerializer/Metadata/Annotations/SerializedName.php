<?php
namespace Opensoft\SimpleSerializer\Metadata\Annotations;

/**
 * @author Evgeny Sugatov
 * @Annotation
 * @Target("PROPERTY")
 */
final class SerializedName
{
    /**
     * @Required
     * @var string
     */
    public $value;
}
